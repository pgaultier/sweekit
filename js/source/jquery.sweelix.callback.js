/**
 * File jquery.sweelix.callback.js
 *
 * This is a callback manager. We can :
 *  - (un)register events to global (or specific) "space"
 *  - raise an event everywhere 
 *
 * Configuration element :
 * <code>
 * {
 * 	'callback':	{
 * 		'globalCblName':'cbl', 
 * 		'top':true
 * 	}
 * }
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2011 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  javascript
 * @package   Sweelix.javascript
 */
(function($) {
	var module = {
		'id': 'callback',
		'major': 1,
		'minor': 2
	};
	var config = { 
		'globalCblName' : 'swlx',
		'top': true
	};
	var attachedCallBacks = {};
	jQuery.sweelix.registerModule(module);

	function argToArray(args) {
		var realArgs = [];
		jQuery.each(args, function(idx, el) {
			realArgs.push(el);
		});
		return realArgs;
	}

	function raiseNamedEvent() {
		var args = argToArray(arguments);
		var name = args.shift();
		var evt = args.shift();
		var i;
		if (typeof(name) === 'string') {
			name = [name];
		}
		for (i = 0; i < name.length; i++) { // in name) {
			var elName = name[i];
			if (typeof(attachedCallBacks[elName]) === 'undefined') {
				jQuery.sweelix.warn('sweelix.%s.raise(%s,%s) : %s.%s() error, handler does not exists', module.id, elName, evt, elName, evt);
			} else {
				if (typeof(attachedCallBacks[elName][evt]) === 'undefined') {
					jQuery.sweelix.warn('sweelix.%s.raise(%s,%s) : %s.%s() error, event does not exists', module.id, elName, evt, elName, evt);
				} else {
					// attachedCallBacks[elName][evt]();
					attachedCallBacks[elName][evt].apply(this, args);
					jQuery.sweelix.info('sweelix.%s.raise(%s,%s) : %s.%s() success', module.id, elName, evt, elName, evt);
				}
			}
		}		
	}
	jQuery.extend(jQuery.sweelix, {
		'callback': {
			'init': function() {
				jQuery.extend(true, config, jQuery.sweelix.config(module.id));
				jQuery.extend(jQuery.sweelix, {
					'register': function() {
						var args = argToArray(arguments);
						var name = config.globalCblName;
						var evts = null;
						var method = null;
						
						if (arguments.length === 2) {
							evts = args.shift();
							method = args.shift();
							jQuery.sweelix.info('sweelix.%s.register(...) event in global space ', module.id);
						} else if (arguments.length === 3) {
							name = args.shift();
							evts = args.shift();
							method = args.shift();
							jQuery.sweelix.info('sweelix.%s.register(...) event in %s space ', module.id, name);
						} else {
							jQuery.sweelix.error('sweelix.%s.register(...) with %d arguments. Expected 2 or 3 arguments', module.id, arguments.length);
							return false;
						} 
						if (typeof(evts) === 'string') {
							evts = [evts];
						}
						jQuery.each(evts, function(i, evt) {
							if (typeof(attachedCallBacks[name]) === 'undefined') {
								attachedCallBacks[name] = {};
								attachedCallBacks[name][evt] = method;
								jQuery.sweelix.info('sweelix.%s.register(%s,%s) : %s.%s() registered and handler created', module.id, name, evt, name, evt);
							} else if (typeof(attachedCallBacks[name][evt]) === 'undefined') {
								attachedCallBacks[name][evt] = method;
								jQuery.sweelix.info('sweelix.%s.register(%s,%s) : %s.%s() registered in existing handler', module.id, name, evt, name, evt);
							} else {
								jQuery.sweelix.warn('sweelix.%s.register(%s,%s) : %s.%s() already registered', module.id, name, evt, name, evt);
							}
						});
					},
					'raiseNamed': function() {
						if ((config.top === true) && (window.top !== window.self)) {
							jQuery.sweelix.warn('sweelix.%s.raise() re-routed to top window', module.id);
							var topObj = window.top.jQuery.sweelix;
							topObj.raise.apply(this, arguments);
						} else {
							if (arguments.length < 2) {
								jQuery.sweelix.error('sweelix.%s.raise(...) with %d arguments', module.id, arguments.length);
							} else {
								// raise here
								raiseNamedEvent.apply(this, arguments);
							}
						}				
					},
					'raise': function() {
						if ((config.top === true) && (window.top !== window.self)) {
							jQuery.sweelix.warn('sweelix.%s. raise() re-routed to top window', module.id);
							var topObj = window.top.jQuery.sweelix;
							topObj.raise.apply(this, arguments);
						} else {
							if (arguments.length < 1) {
								jQuery.sweelix.error('sweelix.%s.raise(...) with %d arguments', module.id, arguments.length);
							} else {
								// raise here
								var args = argToArray(arguments);
								args.unshift(config.globalCblName);
								raiseNamedEvent.apply(this, args);
							}
						}
					},
					'list': function() {
						var regEvent;
						var regListener;
						jQuery.sweelix.group('sweelix.%s Active callbacks', module.id);
						for (regListener in attachedCallBacks) {
							for (regEvent in attachedCallBacks[regListener]) {
								jQuery.sweelix.info('sweelix.%s.%s.%s()', module.id, regListener, regEvent);
							}
						}
						jQuery.sweelix.groupEnd();
					},
					'unregister': function() {
						var args = argToArray(arguments);
						var name = config.globalCblName;
						var evt = null;
						if (arguments.length === 1) {
							jQuery.sweelix.info('sweelix.%s.unregister(...) event in global space ', module.id);
							evt = args.shift();
						} else if (arguments.length === 2) {
							jQuery.sweelix.info('sweelix.%s.unregister(...) event in %s space ', module.id, name);
							name = args.shift();
							evt = args.shift();
						} else {
							jQuery.sweelix.error('sweelix.%s.register(...) with %d arguments. Expected 2 or 3 arguments', module.id, arguments.length);
							return false;
						} 
						if (typeof(attachedCallBacks[name]) === 'undefined') {
							jQuery.sweelix.warn('sweelix.%s.unregister(%s,%s) : %s.%s() error, handler does not exists', module.id, name, evt, name, evt);
						} else {
							if (typeof(attachedCallBacks[name][evt]) === 'undefined') {
								jQuery.sweelix.warn('sweelix.%s.unregister(%s,%s) : %s.%s() error, event does not exists', module.id, name, evt, name, evt);
							} else {
								delete attachedCallBacks[name][evt];
								jQuery.sweelix.info('sweelix.%s.unregister(%s,%s) : %s.%s() success', module.id, name, evt, name, evt);
								var remainingEvents = false;
								var regEvent;
								for (regEvent in attachedCallBacks[name]) {
									remainingEvents = true;
								}
								if (!remainingEvents) {
									delete attachedCallBacks[name];
									jQuery.sweelix.info('sweelix.%s.unregister(%s,...) : handler %s fully unregistered', module.id, name);
								}
							}
						}
					}
				});
				jQuery.sweelix.register('redirect', function(params){
					if(typeof(params) == 'object'){
						setTimeout(function(){document.location.href = params.url;}, params.timer*1000); 
					} else {
						document.location.href = params;
					}
				});
				jQuery.sweelix.info('sweelix.%s : init module version (%d.%d)', module.id, module.major, module.minor);
			}
		}
	});
})(jQuery);

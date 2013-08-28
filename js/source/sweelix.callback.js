/**
 * File sweelix.callback.js
 *
 * This is a simple "trace" / "debug" script. It allow
 * debug process whithout breaking the page
 * <code>
 * {
 * 	'callback':	{
 * 		'globalCblName':'swlx', 
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
(function($s){
	// var args = Array.prototype.slice.call(arguments);
	function Callback(sweelix){
		this.version = "1.0";
		this.id = "callback";
		var config = { 'globalCblName' : 'swlx', 'top':true};
		
		this.reconfigure = function() {
			config = sweelix.config(this.id);
			config['globalCblName'] = (!!config['globalCblName'])?config['globalCblName']:'swlx';
			config['top'] = (typeof(config['top']) != 'undefined')?config['top']:true;
			
			sweelix.register('redirect', function(params){
				if(typeof(params) == 'object'){
					setTimeout(function(){document.location.href = params.url;}, params.timer*1000);
				} else {
					document.location.href = params;
		        }
			});				
			
		};
		var attachedCallBacks = {};

		function raiseNamedEvent() {
			var args = Array.prototype.slice.call(arguments);
			var name = args.shift();
			var evt = args.shift();
			var i;
			if (typeof(name) === 'string') {
				name = [name];
			}
			for (i = 0; i < name.length; i++) { // in name) {
				var elName = name[i];
				if (typeof(attachedCallBacks[elName]) === 'undefined') {
					sweelix.warn('sweelix.raise('+elName+','+evt+') : '+elName+'.'+evt+'() error, handler does not exists');
				} else {
					if (typeof(attachedCallBacks[elName][evt]) === 'undefined') {
						sweelix.warn('sweelix.raise('+elName+','+evt+') : '+elName+'.'+evt+'() error, event does not exists');
					} else {
						// attachedCallBacks[elName][evt]();
						attachedCallBacks[elName][evt].apply(this, args);
						sweelix.info('sweelix.raise('+elName+','+evt+') : '+elName+'.'+evt+'() success');
					}
				}
			}		
		}
		
		this.register = function() {
			var args = Array.prototype.slice.call(arguments);
			var name = config.globalCblName;
			var evts = null;
			var method = null;
			
			if (arguments.length === 2) {
				evts = args.shift();
				method = args.shift();
				sweelix.info('sweelix.register(...) event in global space ');
			} else if (arguments.length === 3) {
				name = args.shift();
				evts = args.shift();
				method = args.shift();
				sweelix.info('sweelix.register(...) event in '+name+' space ');
			} else {
				sweelix.error('sweelix.register(...) with '+args.length+' arguments. Expected 2 or 3 arguments');
				return false;
			} 
			if (typeof(evts) === 'string') {
				evts = [evts];
			}
			for(var i in evts) {
				var evt = evts[i];
				if (typeof(attachedCallBacks[name]) === 'undefined') {
					attachedCallBacks[name] = {};
					attachedCallBacks[name][evt] = method;
					sweelix.info('sweelix.register('+name+','+evt+') : '+name+'.'+evt+'() registered and handler created');
				} else if (typeof(attachedCallBacks[name][evt]) === 'undefined') {
					attachedCallBacks[name][evt] = method;
					sweelix.info('sweelix.register('+name+','+evt+') : '+name+'.'+evt+'() registered in existing handler');
				} else {
					sweelix.warn('sweelix.register('+name+','+evt+') : '+name+'.'+evt+'() already registered');
				}
			}
		};

		this.raiseNamed = function() {
			if ((config.top === true) && (window.top !== window.self)) {
				sweelix.warn('sweelix.raise() re-routed to top window');
				var topObj = window.top.sweelix;
				topObj.raise.apply(this, arguments);
			} else {
				if (arguments.length < 2) {
					sweelix.error('sweelix.raise(...) with '+arguments.length+' arguments');
				} else {
					// raise here
					raiseNamedEvent.apply(this, arguments);
				}
			}				
		};

		this.raise = function() {
			if ((config.top === true) && (window.top !== window.self)) {
				sweelix.warn('sweelix.raise() re-routed to top window');
				var topObj = window.top.sweelix;
				topObj.raise.apply(this, arguments);
			} else {
				if (arguments.length < 1) {
					sweelix.error('sweelix.raise(...) with '+arguments.length+' arguments');
				} else {
					// raise here
					var args = Array.prototype.slice.call(arguments);
					args.unshift(config.globalCblName);
					raiseNamedEvent.apply(this, args);
				}
			}
		};
		
		this.list = function() {
			var regEvent;
			var regListener;
			sweelix.info('sweelix.callback Active callbacks');
			for (regListener in attachedCallBacks) {
				for (regEvent in attachedCallBacks[regListener]) {
					sweelix.info('sweelix.'+regListener+'.'+regEvent+'()');
				}
			}
		};
		
		this.unregister = function() {
			var args = Array.prototype.slice.call(arguments);
			var name = config.globalCblName;
			var evt = null;
			if (arguments.length === 1) {
				sweelix.info('sweelix.unregister(...) event in global space ');
				evt = args.shift();
			} else if (arguments.length === 2) {
				sweelix.info('sweelix.unregister(...) event in '+name+' space ');
				name = args.shift();
				evt = args.shift();
			} else {
				sweelix.error('sweelix.register(...) with '+args.length+' arguments. Expected 2 or 3 arguments');
				return false;
			} 
			if (typeof(attachedCallBacks[name]) === 'undefined') {
				sweelix.warn('sweelix.unregister('+name+','+evt+') : '+name+'.'+evt+'() error, handler does not exists');
			} else {
				if (typeof(attachedCallBacks[name][evt]) === 'undefined') {
					sweelix.warn('sweelix.unregister('+name+','+evt+') : '+name+'.'+evt+'() error, event does not exists');
				} else {
					delete attachedCallBacks[name][evt];
					sweelix.info('sweelix.unregister('+name+','+evt+') : '+name+'.'+evt+'() success');
					var remainingEvents = false;
					var regEvent;
					for (regEvent in attachedCallBacks[name]) {
						remainingEvents = true;
					}
					if (!remainingEvents) {
						delete attachedCallBacks[name];
						sweelix.info('sweelix.unregister('+name+',...) : handler fully unregistered');
					}
				}
			}
		};
		
	};

	var c = new Callback($s);
	$s.register = function(){c.register.apply(c, arguments); };
	$s.raiseNamed = function(){c.raiseNamed.apply(c, arguments); };
	$s.raise = function(){c.raise.apply(c, arguments); };
	$s.callbackList = function(){c.list.apply(c, arguments); };
	$s.unregister = function(){c.unregister.apply(c, arguments); };
	$s.list = function(){c.list.apply(c, arguments); };
	$s.registerModule(c);
	
})(sweelix);


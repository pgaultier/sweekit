/**
 * File jquery.sweelix.debug.js
 *
 * This is a simple "trace" / "debug" script. It allow
 * debug process whithout breaking the page
 * <code>
 * {
 * 	'debug':	{
 * 		'debug':true, 
 * 		'fallback':false,
 * 		'method':'log'
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
		'id': 'debug',
		'major': 1,
		'minor': 2
	};

	function isMethodAvailable(methodName) {
		var _method = methodName || config.method;
		return ((typeof(console) === 'object') && (typeof(console[_method]) !== "undefined"));
	}

	function consoleWrapper() {
		if (!config.debug) { return false; }
		var args = argToArray(arguments);
		var _method = args.shift();
		var i;
		var result;
		if (isMethodAvailable(_method)) {
			try {
				window.top.console[_method].apply(this, args);
			} catch (e) {
				for (i = 0, l = args.length; i < l; i++) {
					window.top.console[_method](args[i]);
				}
			}
		} else if (config.fallback && args.length) {
			result = _method + ': ';
			for (i = 0, l = args.length; i < l; i++) {
				result += args[i] + ' (' + typeof args[i] + ') ';
			}
			alert(result);
		}
	}

	function argToArray(args) {
		var realArgs = [];
		jQuery.each(args, function(idx, el) {
			realArgs.push(el);
		});
		return realArgs;
	}
	
	var config = {
		'debug': false,
		'fallback': false,
		'method': 'log'
	};
	
	jQuery.sweelix.registerModule(module);

	jQuery.extend(jQuery.sweelix, {
		'debug': {
			'init': function() {
				jQuery.extend(true, config, jQuery.sweelix.config(module.id));
				jQuery.extend(jQuery.sweelix, {
					'log': function() {
						var args = argToArray(arguments);
						args.unshift(config.method);
						consoleWrapper.apply(this, args);
					},
					'debug': function() {
						var args = argToArray(arguments);
						args.unshift('debug');
						consoleWrapper.apply(this, args);
					},
					'info': function() {
						var args = argToArray(arguments);
						args.unshift('info');
						consoleWrapper.apply(this, args);
					},
					'warn': function() {
						var args = argToArray(arguments);
						args.unshift('warn');
						consoleWrapper.apply(this, args);
					},
					'error': function() {
						var args = argToArray(arguments);
						args.unshift('error');
						consoleWrapper.apply(this, args);
					},
					'assert': function() {
						var args = argToArray(arguments);
						args.unshift('assert');
						consoleWrapper.apply(this, args);
					},
					'dir': function() {
						var args = argToArray(arguments);
						args.unshift('dir');
						consoleWrapper.apply(this, args);
					},
					'trace': function() {
						var args = argToArray(arguments);
						args.unshift('trace');
						consoleWrapper.apply(this, args);
					},
					'count': function() {
						var args = argToArray(arguments);
						args.unshift('count');
						consoleWrapper.apply(this, args);
					},
					'group': function() {
						var args = argToArray(arguments);
						args.unshift('group');
						consoleWrapper.apply(this, args);
					},
					'groupEnd': function() {
						var args = argToArray(arguments);
						args.unshift('groupEnd');
						consoleWrapper.apply(this, args);
					}
				});
				jQuery.sweelix.info('sweelix.%s : init module version (%d.%d)', module.id, module.major, module.minor);
			}
		}
	});
})(jQuery);

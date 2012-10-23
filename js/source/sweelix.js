/**
 * File jquery.sweelix.js
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2011 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  javascript
 * @package   Sweelix.javascript
 */
var sweelix = (function() {
	function Sweelix(){
		var config = window['sweelixConfig'] || {};
		var loadedModules= [];
		var registeredModules = {};
		var inArray = function(needle, haystack) {
			var status = false;
			for(key in haystack) {
				if(haystack[key] === needle) {
					status = true;
					break;
				}
			}
			return status;
		};
		this.configure = function(params) {
			config = params || {};
			for(var module in registeredModules) {
				if(typeof(registeredModules[module].reconfigure) == 'function') {
					registeredModules[module].reconfigure(config);
					if(!!this.info) this.info('sweelix.reconfigure('+module+')');
				}
			}
		};
		this.registerModule = function(module) {
			if(this.hasModule(module.id) === false) {
				registeredModules[module.id] = module;
				if(typeof(this.info) == 'function') {
					this.info('sweelix.registerModule : '+module.id+' ('+module.version+')');
				}
			}
		};
		this.hasModule = function(moduleId) {
			return (typeof(registeredModules[moduleId]) == 'object');
			return inArray(moduleId, loadedModules);
		};
		this.config = function(moduleId) {
			if (config[moduleId]) {
				return config[moduleId];
			} else {
				return {};
			}
		};
		this.noConflict = function() {
			if(window['$s']) {
				delete(window['$s']);
			}
			return this;
		};
		this.noConflictExtreme = function() {
			if(window['$s']) {
				delete(window['$s']);
			}
			if(window['sweelix']) {
				delete(window['sweelix']);
			}
			return this;
			
		}
	};
	var sweelix = new Sweelix();
	sweelix.version = "1.0";
	sweelix.id = "sweelix";
	window.sweelix = window.$s = sweelix;
	return sweelix;
})();

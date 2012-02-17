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
(function($) {
	var config = {
	};
	var activatedModules = [];

	function in_array(needle, haystack) {
		var status = false;
		jQuery.each(haystack, function(i, el) {
			if (el === needle) {
				status = true;
			}
		});
		return status;
	}

	jQuery.extend(jQuery, {
		'sweelix': {
			'init': function(params) {
				if(params) {
					jQuery.extend(true, config, params);
				}
				jQuery.each(activatedModules, function(i, el) {
					jQuery.sweelix[el].init();
				});
			},
			'registerModule': function(module) {
				if (in_array(module.id, activatedModules) === false) {
					activatedModules.push(module.id);
				}
			},
			'config': function(module) {
				if (config[module]) {
					return config[module];
				} else {
					return {};
				}
			}
		}
	});
})(jQuery);
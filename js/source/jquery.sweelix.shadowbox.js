/**
 * File jquery.sweelix.ajax.js
 *
 * This is a simple ajax helper
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
		'id': 'shadowbox',
		'major': 1,
		'minor': 0
	};
	
	var config = { };
	
	jQuery.sweelix.registerModule(module);

	jQuery.extend(jQuery.sweelix, {
		'shadowbox': {
			'init': function() {
				jQuery.extend(true, config, jQuery.sweelix.config(module.id));
				/**
				 * Open Shadowbox
				 * 
				 * @param mixed params use classic shadowbox parameters
				 */
				jQuery.sweelix.register('shadowboxOpen',function(params) {
					if(typeof(Shadowbox) == 'undefined') {
						setTimeout(function(){ jQuery.sweelix.raise('shadowBox', params); }, 250);
					} else {
						Shadowbox.open(params);
					}
				});
				/**
				 * Close Shadowbox
				 */
				jQuery.sweelix.register('shadowboxClose',function() {
					Shadowbox.close();
				});
				jQuery.sweelix.info('sweelix.%s : init module version (%d.%d)', module.id, module.major, module.minor);
			}
		}
	});
})(jQuery);

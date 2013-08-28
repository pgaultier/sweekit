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
if(typeof(sweelix) != 'object') {
	throw "Sweelix is missing";
}
(function($s){
	function SbWrapper(sweelix){
		this.version = "1.0";
		var config = sweelix.config('shadowbox');
		sweelix.register('shadowboxOpen', function(params) {
			if(typeof(Shadowbox) == 'undefined') {
				setTimeout(function(){ sweelix.raise('shadowBox', params); }, 250);
			} else {
				Shadowbox.open(params);
			}
		});
		sweelix.register('shadowboxClose',function() {
			Shadowbox.close();
		});
	};
	var sb = new SbWrapper($s);
	$s.info('sweelix.shadowbox : register module version ('+sb.version+')');

})(sweelix)


/**
 * File jquery.sweelix.debug.js
 *
 * This is a simple "trace" / "debug" script. It allow
 * debug process whithout breaking the page
 * <code>
 * {
 * 	'debug':	{
 * 		'appender':['browser']
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



/*
sample to replace ajaxappender
function JsonAppender(url) {
    var isSupported = true;
    var successCallback = function(data, textStatus, jqXHR) { return; };
    if (!url) {
        isSupported = false;
    }
    this.setSuccessCallback = function(successCallbackParam) {
        successCallback = successCallbackParam;
    };
    this.append = function (loggingEvent) {
        if (!isSupported) {
            return;
        }
        $.post(url, {
            'logger': loggingEvent.logger.name,
            'timestamp': loggingEvent.timeStampInMilliseconds,
            'level': loggingEvent.level.name,
            'url': window.location.href,
            'message': loggingEvent.getCombinedMessages(),
            'exception': loggingEvent.getThrowableStrRep()
        }, successCallback, 'json');
    };
}

JsonAppender.prototype = new log4javascript.Appender();
JsonAppender.prototype.toString = function() {
    return 'JsonAppender';
};
log4javascript.JsonAppender = JsonAppender;

*/

(function($s){
	function Debug(sweelix){
		this.version = "1.0";
		this.id = "debug";
		var config = {};
		var log = log4javascript.getLogger();
		this.reconfigure = function(config) {
			log.removeAllAppenders()
			config = sweelix.config(this.id);
			if(config['appenders']) {
				config['appenders'] = (config['appenders'] instanceof Array)?config['appenders']:[config['appenders']];
				for(var i in config['appenders']) {
					log.addAppender(config['appenders'][i]);
				}
			}
		}
		this.trace = function(){log.trace.apply(log, arguments); };
		this.debug = function(){log.debug.apply(log, arguments); };
		this.info = function(){log.info.apply(log, arguments); };
		this.warn = function(){log.warn.apply(log, arguments); };
		this.error = function(){log.error.apply(log, arguments); };
		this.fatal = function(){log.fatal.apply(log, arguments); };
		this.reconfigure();
	};
	var d = new Debug($s);
	$s.trace = function(){d.trace.apply(d, arguments); };
	$s.debug = function(){d.debug.apply(d, arguments); };
	$s.info = function(){d.info.apply(d, arguments); };
	$s.warn = function(){d.warn.apply(d, arguments); };
	$s.error = function(){d.error.apply(d, arguments); };
	$s.fatal = function(){d.fatal.apply(d, arguments); };
	$s.registerModule(d)

})(sweelix);


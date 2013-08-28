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

(function($s, $){
	function Ajax(sweelix){
		this.version = "1.0";
		this.id = "ajax";
		var config = {};
		
		this.reconfigure = function() {
			config = sweelix.config('ajax');
		}
	};
	var a = new Ajax($s);
	
	/**
	 * Handle ajax update for form. Allow classic renderPartial update or direct javascript eval
	 * 
	 * @param string targetSelector the targetSelector allow event delegation. targetSelector is the form element
	 */
	$.fn.ajaxSubmitHandler = function (targetSelector) {
		targetSelector = targetSelector || null; 
		var replace = false
		if(arguments.length == 2) {
			replace = (!!arguments[1] );
		}
		var button = {
			'target' : 'input:submit, input:image, button:submit',
			'clicked' : 'input[data-ajaxclick="true"], button[data-ajaxclick="true"]'
		};
		
		//No delegated mode
		if(targetSelector == null) {
			$s.info('jQuery('+this.selector+').ajaxSubmitHandler()');
			return this.each(function () {
				var currentForm = $(this);
				$(currentForm).on('click', button.target, function(evt) {
					//jQuery(button.clicked, currentForm).removeAttr("data-ajaxclick");
					$(evt.target).attr("data-ajaxclick", "true");
				});
				$(this).on('submit', function(evt) {
					evt.preventDefault();
					$(this).trigger('beforeAjax');
					var data = $(this).serializeArray();
					var cButton = $(button.clicked);
					if(cButton.length > 0) {
						var b = {
							'name' : cButton.attr('name'),
							'type' : cButton.attr('type').toLowerCase()
						};
						if(b.type == 'image') {
							data.push({
								'name' : b.name+'_x',
								'value' : 1
							});
							data.push({
								'name' : b.name+'_y',
								'value' : 1
							});
						} else {
							data.push({
								'name' : b.name,
								'value' : cButton.val()
							});
						}
						cButton.removeAttr("data-ajaxclick");
					}
					var targetUrl = $(this).attr('action');
					if(typeof(targetUrl) == 'undefined') {
						targetUrl = $(location).attr('href');
					}
					$.ajax({
						headers: { 
							'Accept' : 'application/javascript;q=0.9,text/html;q=0.8,*/*;q=0.5'
						},
						'data':data,
						'url':targetUrl,
						'type':'POST',
						'context':this,
						'success':function(data, status, xhr){
						},
						'complete':function(xhr, status, data) {
							var element = this;
							switch(xhr.getResponseHeader('Content-Type')) {
								case 'application/javascript' :
									break;
								case 'text/html' :
								default :
									if(replace == true) {
										element = $(xhr.responseText);
										$(this).replaceWith(element);
									} else {
										$(this).html(xhr.responseText);
									}
									break;
							}
							$(element).trigger('afterAjax');
						}
					});
				});
			});
		//Delegated mode
		} else {
			$s.info('jQuery('+this.selector+').ajaxSubmitHandler('+targetSelector+')');
			
			//Add delegate target to default buttons
			var aButton = {
				'target' : button.target.split(','),
				'clicked' : button.clicked.split(','),
			};
			
			var tmp = '';
			var sep = '';
			$(aButton.target).each(function(idx, el) {
				tmp += sep + targetSelector + ' ' + el;
				sep = ', ';
			});
			aButton.target = tmp;
			
			var tmp = '';
			var sep = '';
			$(aButton.clicked).each(function(idx, el) {
				tmp += sep + targetSelector + ' ' + el;
				sep = ', ';
			});
			aButton.clicked = tmp;
			
			return this.each(function (idx, el) {
				//Detect and memorize click
				$(this).on('click', aButton.target, function(evt) {
					$(evt.target).attr("data-ajaxclick", "true");
				})
				
				//Handel delegated ajax submit
				$(this).on('submit', targetSelector, function(evt) {
					evt.preventDefault();
					
					//Raise before ajax event
					$(this).trigger('beforeAjax');
					
					//Get data
					var data = $(evt.target).serializeArray();
					
					//Add button name/value to data
					var cButton = $(aButton.clicked);
					if(cButton.length > 0) {
						var b = {
							'name' : cButton.attr('name'),
							'type' : cButton.attr('type').toLowerCase()
						};
						if(b.type == 'image') {
							data.push({
								'name' : b.name+'_x',
								'value' : 1
							});
							data.push({
								'name' : b.name+'_y',
								'value' : 1
							});
						} else {
							data.push({
								'name' : b.name,
								'value' : cButton.val()
							});
						}
						cButton.removeAttr("data-ajaxclick");
					}
					
					//Define target url
					var targetUrl = $(this).attr('action');
					if(typeof(targetUrl) == 'undefined') {
						targetUrl = $(location).attr('href');
					}
					
					//Compute ajax call
					$.ajax({
						headers: { 
							'Accept' : 'application/javascript;q=0.9,text/html;q=0.8,*/*;q=0.5'
						},
						'data':data,
						'url':targetUrl,
						'type':'POST',
						'context':this,
						'success':function(data, status, xhr){
						},
						'complete':function(xhr, status, data) {
							var element = this;
							switch(xhr.getResponseHeader('Content-Type')) { 
								case 'application/javascript' :
									break;
								case 'text/html' :
								default :
									if(replace == true) {
										element = $(xhr.responseText);
										$(this).replaceWith(element);
									} else {
										$(this).html(xhr.responseText);
									}
									break;
							}
							$(element).trigger('afterAjax');
						}
					});
				});
			});
			
		}
	};

	/**
	 * Refresh part
	 * 
	 * @param mixed params array('targetUrl' => url, 'data' => 'post data if needed', 'mode' => 'can be replace or empty', 'targetSelector' => 'where to render content')
	 */
	$s.register('ajaxRefreshHandler',function(params) {
		$s.info('sweelix.ajaxRefreshHandler(...)');
		$(params.targetSelector).trigger('beforeAjax');
		$.ajax({
			headers: { 
				'Accept' : 'application/javascript;q=0.9,text/html;q=0.8,*/*;q=0.5'
			},
			'url':params.targetUrl,
			'type':'POST',
			'data':params.data,
			'success':function(data, status, xhr){
				switch(xhr.getResponseHeader('Content-Type')) {
					case 'application/javascript' :
						break;
					case 'text/html' :
					default :
						if(params.mode == 'replace') {
							$(params.targetSelector).replaceWith(data);
						} else {
							$(params.targetSelector).html(data);
						}
						break;
				}
				$(params.targetSelector).trigger('afterAjax');
			}
		});
	});
	
	$s.registerModule(a);

})(sweelix, jQuery);


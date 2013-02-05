/**
 * File sweelix.notice.js
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
	function Notice(sweelix){
		this.version = "1.0";
		this.id = "notice";
		var config = {
			'cssClass':'',
			'keepAlive': 6000, // hang on duration
			'fadeSpeed':500,
			'sticky':false
		};
		var templates = {
			'wrapper':'<div id="notice-wrapper"></div>',
			'title':'<span class="title">{{title}}</span>',
			'item':'<div id="notice-item-{{num}}" class="notice-item {{cssClass}}" style="display:none">{{close}}{{title}}<p>{{text}}</p></div>',
			'close':'<a href="javascript:void(0);" class="close">{{close}}</a>'
		};
		var noticeCount = 0;
		
		function checkWrapper() {
			if(jQuery('#notice-wrapper').length == 0){
				jQuery('body').append(templates.wrapper);
			}
		}
		
		/**
		 * An extremely handy PHP function ported to JS, works well for templating
		 * @private
		 * @param {String/Array} search A list of things to search for
		 * @param {String/Array} replace A list of things to replace the searches with
		 * @return {String} sa The output
		 */  
		function str_replace(search, replace, subject, count){
			var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
				f = [].concat(search),
				r = [].concat(replace),
				s = subject,
				ra = r instanceof Array, sa = s instanceof Array;
			s = [].concat(s);
			if(count){
				this.window[count] = 0;
			}
			for(i = 0, sl = s.length; i < sl; i++){
				if(s[i] === ''){
					continue;
				}
				for (j = 0, fl = f.length; j < fl; j++){
					temp = s[i] + '';
					repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
					s[i] = (temp).split(f[j]).join(repl);
					if(count && s[i] !== temp){
						this.window[count] += (temp.length-s[i].length) / f[j].length;
					}
				}
			}
			return sa ? s : s[0];
		}
		
		
		
		this.add = function(parameters){
			if(typeof(parameters) == 'string'){
				parameters = {'text':parameters};
			}
			if(typeof(parameters.text) == 'undefined') {
				sweelix.error('Text is missing');
			}
			var title = (typeof(parameters.title) !== 'undefined')?parameters.title:''; 
			var close = (typeof(parameters.close) !== 'undefined')?parameters.close:'x'; 
			var text = parameters.text;
			var sticky = (typeof(parameters.sticky) !== 'undefined')?parameters.sticky:config.sticky;
			var cssClass = (typeof(parameters.cssClass) !== 'undefined')?parameters.cssClass:config.cssClass;
			var keepAlive = (typeof(parameters.keepAlive) !== 'undefined')?parameters.keepAlive:config.keepAlive;
			noticeCount++;
			var itemNum = noticeCount;
			var notice = templates.item;
			checkWrapper();
			// should handle callbacks
			// Reset
			// this._custom_timer = 0;
			
			// A custom fade time set
			// if(time_alive){
			//	this._custom_timer = time_alive;
			// }
			
			if(title.length > 0) {
				title = str_replace('{{title}}', title, templates.title);
			}
			close = str_replace('{{close}}', close, templates.close);
			notice = str_replace(['{{title}}', '{{text}}', '{{close}}', '{{num}}', '{{cssClass}}'], [title, text, close, itemNum, cssClass], notice);
			
			// If it's false, don't show another gritter message
			// if(this['_before_open_' + number]() === false){
			// 	return false;
			// }
			jQuery('#notice-wrapper').append(notice);
			var item = jQuery('#notice-item-' + itemNum);
			item.fadeIn(config.fadeSpeed, function(){
				// Gritter['_after_open_' + number]($(this));
			});
			if(!sticky){
				// this._setFadeTimer(item, number);
				remove(item, keepAlive);
			}
			item.find('.close').on('click', function(evt){evt.preventDefault(); remove(item, 0);});
			return itemNum;
		};
		
		this.remove = function(itemNum) {
			var item = jQuery('#notice-item-' + itemNum);
			remove(item, 0);
		}
		
		function remove(item, keepAlive) {
			if(keepAlive == 0) {
				item.fadeOut(config.fadeSpeed, function(){
					jQuery(this).remove();
				});
			} else {
				setTimeout(function(){
					item.fadeOut(config.fadeSpeed, function(){
						jQuery(this).remove();
					});
				}, keepAlive);
			}
		}
		
		this.reconfigure = function(config) {
			var self = this;
			config = sweelix.config(this.id);
			if($s.hasModule('callback') == true) {
				$s.unregister('showNotice');
				$s.unregister('removeNotice');
				$s.register('showNotice',function(params){
					self.add(params);
				});
				$s.register('removeNotice',function(params){
					self.remove(params);
				});
			}
		};
		
		
	};
	var n = new Notice($s);
	$s.showNotice = function(){n.add.apply(n, arguments); };
	$s.removeNotice = function(){n.remove.apply(n, arguments); };
	$s.registerModule(n);
	
	n.reconfigure();

})(sweelix);


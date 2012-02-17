/**
 * File jquery.sweelix.plupload.js
 *
 * This is the default handler for plupload
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
		'id': 'plupload',
		'major': 1,
		'minor': 0
	};
	var config = { };

	function isValidDrag(e) {
		var dt = e.dataTransfer;
		// do not check dt.types.contains in webkit, because it crashes safari 4
		var isWebkit = navigator.userAgent.indexOf("AppleWebKit") > -1;

		// dt.effectAllowed is none in Safari 5
		// dt.types.contains check is for firefox
		return dt && dt.effectAllowed != 'none' && (dt.files || (!isWebkit && dt.types.contains && dt.types.contains('Files')));
	}
	
	jQuery.sweelix.registerModule(module);

	jQuery.extend(jQuery.sweelix, {
		'plupload': {
			'init': function() {
				jQuery.extend(true, config, jQuery.sweelix.config(module.id));
				jQuery.sweelix.info('sweelix.%s : init module version (%d.%d)', module.id, module.major, module.minor);
			},
			'postInit': function (up) {
				jQuery('#'+up.getId()).before('<div id="'+up.getZoneId()+'" class="dropZone" style="display:none">'+up.getZoneText()+'</div>');
				jQuery('#'+up.getId()).after('<div id="'+up.getListId()+'" class="filesContainer"> </div>');
				document.addEventListener('dragenter', function(e){
					if(!isValidDrag(e)) return;
					jQuery('.dropZone').show();
				}, false);
				document.addEventListener('dragleave', function(e){
					if(!isValidDrag(e)) return;
		            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
		            // only fire when leaving document out
		            if ( ! relatedTarget || relatedTarget.nodeName == "HTML"){
		            	jQuery('.dropZone').hide();
		            }
				}, false);
				jQuery('.dropZone').bind('dragenter', function(e){
					if(jQuery(this).hasClass('hover') == false) {
						jQuery(this).addClass('hover');
					}
				});
				jQuery('.dropZone').bind('dragover', function(e){
					if(jQuery(this).hasClass('hover') == false) {
						jQuery(this).addClass('hover');
					}
		            var effect = e.originalEvent.dataTransfer.effectAllowed;
		            if (effect == 'move' || effect == 'linkMove'){
		            	e.originalEvent.dataTransfer.dropEffect = 'move'; // for FF (only move allowed)
		            } else {
		            	e.originalEvent.dataTransfer.dropEffect = 'copy'; // for Chrome
		            }
				});
				jQuery('.dropZone').bind('dragleave', function(e){
					if(jQuery(this).hasClass('hover') == true) {
						jQuery(this).removeClass('hover');
					}
				});
				jQuery('.dropZone').bind('drop', function(evt){
					jQuery('.dropZone').hide().removeClass('hover');
				});
			},
			'filesAdded' : function (up, files) {
				jQuery.each(files,  function(i, file){ 
					jQuery('#'+up.getListId()).append('<div id="'+ file.id + '" class="fileContainer" title="'+file.name+'">' + up.cutName(file.name) + ' ('+ up.formatSize(file.size) +')<div class="progressBar"><div class="progress"></div></div></div>');
				});
				up.refresh();
			},
			'filesRemoved' : function (up, files) {
				jQuery.each(files,  function(i, file){ 
					jQuery('#'+file.id).fadeOut('slow', function(){ jQuery(this).remove(); });
				});
			},
			'uploadProgress' : function (up, file) {
				jQuery('#'+up.getListId()+' #'+file.id+' div.progress').css({width:file.percent+'%'});
			},
			'error' : function(up, err) {
				alert(err.message);
				up.refresh();
			},
			'fileUploaded' : function (up, file, response) {
				var json = jQuery.parseJSON(response.response); 
				if(json.status == false) { 
					jQuery('#'+up.getListId()+' #'+file.id).fadeOut('slow', function(){ jQuery(this).remove(); }); 
				} else { 
					if(up.getMultiSelection()==false) {
						jQuery('#'+up.getListId()+' .fileContainer').each(function(idx, el){
							if($(el).attr('id') != file.id) {
								$(el).remove();
							}
						});
					}
					jQuery('#'+up.getListId()+' #'+file.id+' div.progress').css({width:'100%'});
					jQuery('#'+up.getListId()+' #'+file.id).prepend('<a href="javascript:void(0);" onClick="jQuery.ajax.asyncDelete(\''+up.getId()+'\', \''+file.id+'\', function(id){ jQuery(\'#\'+id).remove();});">X</a>');
				} 
			}	
		}
	});
})(jQuery);

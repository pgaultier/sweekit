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
(function($s, $){
	function PlUpload($){
		this.version = "1.0";
		var config = sweelix.config('plupload');
		
		function isValidDrag(e) {
			var dt = e.dataTransfer;
			// do not check dt.types.contains in webkit, because it crashes safari 4
			var isWebkit = navigator.userAgent.indexOf("AppleWebKit") > -1;

			// dt.effectAllowed is none in Safari 5
			// dt.types.contains check is for firefox
			return dt && dt.effectAllowed != 'none' && (dt.files || (!isWebkit && dt.types.contains && dt.types.contains('Files')));
		}
		this.postInit = function (up) {
			$('#'+up.getId()).before('<div id="'+up.getZoneId()+'" class="dropZone" style="display:none">'+up.getZoneText()+'</div>');
			$('#'+up.getId()).after('<div id="'+up.getListId()+'" class="filesContainer"> </div>');
			document.addEventListener('dragenter', function(e){
				if(!isValidDrag(e)) return;
				$('.dropZone').show();
			}, false);
			document.addEventListener('dragleave', function(e){
				if(!isValidDrag(e)) return;
	            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
	            // only fire when leaving document out
	            if ( ! relatedTarget || relatedTarget.nodeName == "HTML"){
	            	$('.dropZone').hide();
	            }
			}, false);
			$('.dropZone').bind('dragenter', function(e){
				if($(this).hasClass('hover') == false) {
					$(this).addClass('hover');
				}
			});
			$('.dropZone').bind('dragover', function(e){
				if($(this).hasClass('hover') == false) {
					$(this).addClass('hover');
				}
	            var effect = e.originalEvent.dataTransfer.effectAllowed;
	            if (effect == 'move' || effect == 'linkMove'){
	            	e.originalEvent.dataTransfer.dropEffect = 'move'; // for FF (only move allowed)
	            } else {
	            	e.originalEvent.dataTransfer.dropEffect = 'copy'; // for Chrome
	            }
			});
			$('.dropZone').bind('dragleave', function(e){
				if($(this).hasClass('hover') == true) {
					$(this).removeClass('hover');
				}
			});
			$('.dropZone').bind('drop', function(evt){
				$('.dropZone').hide().removeClass('hover');
			});
		};
		this.filesAdded = function (up, files) {
			$.each(files,  function(i, file){ 
				$('#'+up.getListId()).append('<div id="'+ file.id + '" class="fileContainer" title="'+file.name+'">' + up.cutName(file.name) + ' ('+ up.formatSize(file.size) +')<div class="progressBar"><div class="progress"></div></div></div>');
			});
			up.refresh();
		};
		this.filesRemoved = function (up, files) {
			$.each(files,  function(i, file){ 
				$('#'+file.id).fadeOut('slow', function(){ $(this).remove(); });
			});
		};
		this.uploadProgress = function (up, file) {
			$('#'+up.getListId()+' #'+file.id+' div.progress').css({width:file.percent+'%'});
		};
		this.error = function(up, err) {
			alert(err.message);
			up.refresh();
		};
		this.fileUploaded = function (up, file, response) {
			var json = $.parseJSON(response.response); 
			if(json.status == false) { 
				$('#'+up.getListId()+' #'+file.id).fadeOut('slow', function(){ $(this).remove(); }); 
			} else { 
				if(up.getMultiSelection()==false) {
					$('#'+up.getListId()+' .fileContainer').each(function(idx, el){
						if($(el).attr('id') != file.id) {
							$(el).remove();
						}
					});
				}
				$('#'+up.getListId()+' #'+file.id+' div.progress').css({width:'100%'});
				$('#'+up.getListId()+' #'+file.id).prepend('<a href="javascript:void(0);" onClick="$s.plupload.asyncDelete(\''+up.getId()+'\', \''+file.id+'\', function(id){ $(\'#\'+id).remove();});">X</a>');
			} 
		};
	}
	$s.plupload = new PlUpload($);
	
	$s.plupload.asyncDelete = function(uploaderId, id, callback){
		var up = window['uploader_'+uploaderId];
		var hiddenId = '#h'+id;
		if(up.getDeleteUrl() != null) {
			jQuery.ajax({
				'url' : up.getDeleteUrl(),
				'data' : {'name':$(hiddenId).val()},
				'success' : function(){
					jQuery(hiddenId).remove();
					var file = {id:id};
					up.removeFile(file);
					if(typeof(callback) == 'function') {
						callback(id);
					}
				}
			});
		} else {
			jQuery(hiddenId).remove();
			var file = {id:id};
			up.removeFile(file);
			if(typeof(callback) == 'function') {
				callback(id);
			}
		}
	};

	function createHiddenField(up, json, file, hiddenId, config) {
		if(json.status == true) {
			if(up.getMultiSelection() == false) {
				jQuery('#'+hiddenId+' input[type=hidden]').each(function(idx, el){
					var fileId = jQuery(el).attr('id');
					fileId = fileId.substring(1);
					$s.plupload.asyncDelete(up.getId(), fileId, function(id){ });
				});
			}
			jQuery('#'+hiddenId).append('<input type="hidden" id="h'+file.id+'" name="'+config.realName+'" value="'+json.fileName+'" />')
		}
	}		

	
	$.fn.asyncUpload = function (config, events) {
		config = config||{};
		events = events||{};
		var baseConfig = { 
			'runtimes' : (!!config.runtimes)?config.runtimes:'flash',
			'multi_selection': (!!config.multiSelection)?config.multiSelection:false,
			'max_file_size': (!!config.maxFileSize)?config.maxFileSize:'10mb',
			'chunk_size':(!!config.chunkSize)?config.chunkSize:'10mb',
			'unique_names':(!!config.uniqueNames)?config.uniqueNames:false,
			'url':config.url,
			'flash_swf_url':(!!config.flashSwfUrl)?config.flashSwfUrl:null,
			'silverlight_xap_url':(!!config.silverlightXapUrl)?config.silverlightXapUrl:null,
			'browse_button':(!!config.browseButton)?config.browseButton:null,
			'drop_element':(!!config.dropElement)?config.dropElement:null,
			'container':(!!config.container)?config.container:null,
			'multipart':(!!config.multipart)?config.multipart:null,
			'multipart_params':(!!config.multipartParams)?config.multipartParams:null,
			'required_features':(!!config.requiredFeatures)?config.requiredFeatures:null,
			'headers':(!!config.headers)?config.headers:null
		};
		if(!!config.filters) {
			baseConfig['filters'] = config.filters;
		}
		var uploadedFiles = (!!config.uploadedFiles)?config.uploadedFiles:null;
		$.extend(baseConfig, {'headers':{'X-Requested-With':'XMLHttpRequest'}});
		return this.each(function () {
			var id = $(this).attr('id');
			var hiddenId = $(this).attr('id')+'_hidden';
			baseConfig['browse_button'] = id;
			var uploader = new plupload.Uploader(baseConfig);
			uploader['getId'] = function() {
				return id;
			};
			uploader['getHiddenId'] = function() {
				return hiddenId;
			};
			uploader['getListId'] = function() {
				return id+'_list';
			};
			uploader['getZoneId'] = function() {
				return id+'_zone';
			};
			uploader['getDeleteUrl'] = function() {
				return (!!config.urlDelete)?config.urlDelete:null;
			};
			uploader['getZoneText'] = function() {
				return (!!config.dropText)?config.dropText:null;
			};
			uploader['getMultiSelection'] = function() {
				return baseConfig.multi_selection;
			};
			uploader['cutName'] = function(fileName){
				var name = fileName;
				if (fileName.length > 24) {
					name = fileName.substr(0,12) + '...' + fileName.substr(fileName.length-12,fileName.length)
				}
				return name;
			};
			uploader['formatSize'] = plupload.formatSize;
			if('PostInit' in events) {
				uploader.bind('PostInit', events.PostInit);
				delete events['PostInit'];
			}
			if(!!config.ui) {
				uploader.bind('PostInit', $s.plupload.postInit);
			}
			uploader.init();
			$('#'+id).append('<div style="display:none;" id="'+hiddenId+'" ></div>');
			$.each(events, function(key, callback) {
				uploader.bind(key, callback);
			});
			uploader.bind('FileUploaded', function(up, file, response) {
				var json = $.parseJSON( response.response );
				createHiddenField(uploader, json, file, hiddenId, config);
			});
			if(!!config.auto) {
				uploader.bind('FilesAdded', function(up, file) {
					up.refresh();
					up.start();
				});
			}
			if(!!config.ui) {
				uploader.bind('FilesAdded', $s.plupload.filesAdded);
				uploader.bind('FilesRemoved', $s.plupload.filesRemoved);
				uploader.bind('UploadProgress', $s.plupload.uploadProgress);
				uploader.bind('FileUploaded', $s.plupload.fileUploaded);
				uploader.bind('Error', $s.plupload.error);
			}
			if(!!uploadedFiles) {
				var jsFiles = [];
				$.each(uploadedFiles, function(idx, file) {
					var jsFile = new plupload.File(plupload.guid(), file.fileName, file.fileSize);
					jsFiles.push(jsFile);
					createHiddenField(uploader, file, jsFile, hiddenId, config);
				});
				if(!!config.ui) {
					$s.plupload.filesAdded(uploader, jsFiles);
					$.each(jsFiles, function(idx, jsFile){
						var response = { 'response' : '{"fileName":"'+jsFile.name+'", "status":true, "size":'+jsFile.size+'}', 'status' : true };
						$s.plupload.fileUploaded(uploader, jsFile, response);
					});
				}
				if('FilesAdded' in events) {
					events.FilesAdded(uploader, jsFiles);
				}
				if('FileUploaded' in events) {
					$.each(jsFiles, function(idx, jsFile){
						var response = { 'response' : '{"fileName":"'+jsFile.name+'", "status":true, "size":'+jsFile.size+'}', 'status' : true };
						events.FileUploaded(uploader, jsFile, response);
					});
				}
				if('UploadComplete' in events) {
					events.UploadComplete(uploader, jsFiles);
				}
				uploader.refresh(); // not sure if this is needed
			}
			window['uploader_'+uploader.getId()] = uploader;
		});
	};
	
})(sweelix, jQuery);

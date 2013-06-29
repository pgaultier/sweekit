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

	function Sweeload() {
		var uploader;
		var self;
		function cutName(fileName){
			var name = fileName;
			if (fileName.length > 24) {
				name = fileName.substr(0,12) + '...' + fileName.substr(fileName.length-12,fileName.length)
			}
			return name;
		}
		function getContainerId() {
			return uploader.getId()+'_list';
		}
		function getDropZoneId() {
			return uploader.getId()+'_zone';
		}
		function formatSize(size) {
			return uploader.formatSize(size)
		}
		function getDeleteUrl() {
			return uploader.getDeleteUrl();
		}
		function getPreviewUrl() {
			return uploader.getPreviewUrl();
		}
		function getConfig() {
			return uploader.getEventHandlerConfig();
		}
		this.Error = function(up, error) {
			alert(error.message);
			switch(error.code) {
				case plupload.GENERIC_ERROR:
					break;
				case plupload.HTTP_ERROR:
					break;
				case plupload.GENERIC_ERROR:
					break;
				case plupload.IO_ERROR:
					break;
				case plupload.SECURITY_ERROR:
					break;
				case plupload.INIT_ERROR:
					break;
				case plupload.FILE_SIZE_ERROR:
					break;
				case plupload.FILE_EXTENSION_ERROR:
					break;
				case plupload.IMAGE_FORMAT_ERROR:
					break;
				case plupload.IMAGE_DIMENSIONS_ERROR:
					break;
				default:
					break;
			}
			
		}
		this.AsyncDelete = function(file, name){

		}
		this.FilesRemoved = function (up, files) {
			$.each(files,  function(i, file){ 
				$('#'+file.id).fadeOut('slow', function(){ $(this).remove(); });
			});
		};	
		this.UploadProgress = function (up, file) {
			$('#'+getContainerId()+' #'+file.id+' div.progress').css({width:file.percent+'%'});
		};
		
		this.PostInit = function(up) {
			uploader = up;
			$('#'+up.getId()).after('<ul id="'+getContainerId()+'" class="filesContainer"> </ul>');
		}
		this.FilesAdded = function (up, files) {
			$.each(files,  function(i, file){ 
				$('#'+getContainerId()).append('<li id="'+ file.id + '" class="fileContainer" title="'+file.name+'">' + cutName(file.name.replace('tmp://', '')) + ' ('+ formatSize(file.size) +')<div class="progressBar"><div class="progress"></div></div></li>');
			});
			// up.refresh();
		};
		this.FileUploaded = function (up, file, response) {
			var json = $.parseJSON(response.response); 
			var name = json.fileName;
			if(json.status == true) { 
				$('#'+getContainerId()+' #'+file.id+' div.progress').css({width:'100%'});
				var remove = $('<a href="#" class="close">X</a>');
				remove.one('click', function(evt){
					evt.preventDefault();
					self.AsyncDelete(file, name);
					uploader.removeFile(file, name);
				});
				$('#'+getContainerId()+' #'+file.id).prepend(remove);
				$.ajax({
					'url' : getPreviewUrl(),
					'data' : {
						'fileName' : name,
						'mode' : 'json'
					}
				}).done(function(data){
					if(data.path != null) {
						var element = $('<a href="'+data.path+'" target="_blank"><img src="'+data.url+'" /></a>')
						if(data.image == false) {
							element.append($('<span>'+data.path+'</span>'));
						}
						var config = getConfig();
						if('linkClass' in config) {
							element.addClass(config['linkClass']);						
						}
						if('store' in config) {
							element.data('store', config['store'])
						}
					} else {
						var element = $('<img src="'+data.url+'" />');
					}
					$('#'+getContainerId()+' #'+file.id).append(element);
				});
				
			} 
		};
		self = this;
		
	}

(function($s, $){
	
	function createHiddenField(up, json, file, hiddenId, config) {
		if(json.status == true) {
			if(up.getMultiSelection() == false) {
				jQuery('#'+hiddenId+' input[type=hidden]').each(function(idx, el){
					var fileId = jQuery(el).attr('id');
					fileId = fileId.substring(1);
					up.asyncDelete(up.getFile(fileId), jQuery(el).val());
				});
			}
			jQuery('#sweeploadEmpty').remove();
			jQuery('#'+hiddenId).append('<input type="hidden" id="h'+file.id+'" name="'+config.realName+'" value="'+json.fileName+'" />')
		}
	}		

	
	$.fn.asyncUpload = function (config) {
		config = config||{};
		eventsHandler = {};
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
			// prepare element : button + hidden container
			var id = $(this).attr('id');
			var hiddenId = $(this).attr('id')+'_hidden';
			baseConfig['browse_button'] = id;
			var uploader = new plupload.Uploader(baseConfig);
			
			if(config.ui == true) {
				if(typeof(SweeploadBasicUI) == 'function') {
					eventsHandler = new SweeploadBasicUI();
				}
			} else if(typeof(config.ui) == 'object') {
				eventsHandler = config.ui;
			}

			// extend the puloader to return needed elements
			uploader['getId'] = function() {
				return id;
			};
			uploader['getHiddenId'] = function() {
				return hiddenId;
			};
			uploader['getDeleteUrl'] = function() {
				return (!!config.urlDelete)?config.urlDelete:null;
			};
			uploader['getPreviewUrl'] = function() {
				return (!!config.urlPreview)?config.urlPreview:null;
			};
			uploader['getEventHandlerConfig'] = function() {
				return (!!config.eventHandlerConfig)?config.eventHandlerConfig:{};
			};
			uploader['getMultiSelection'] = function() {
				return baseConfig.multi_selection;
			};

			uploader['formatSize'] = plupload.formatSize;
			
			uploader['asyncDelete'] = function(file, uploadedName){
				if(uploader.getDeleteUrl() != null) {
					jQuery.ajax({
						'url' : uploader.getDeleteUrl(),
						'data' : {'name':uploadedName},
					}).done(function(data, textStatus, jqXHR){
						if(('AsyncDelete' in eventsHandler) && (typeof(eventsHandler.AsyncDelete) == 'function')) {
							eventsHandler.AsyncDelete(file, uploadedName);
						}
					}).always(function() { 
						uploader.removeFile(file);
					});
				} else {
					uploader.removeFile(file);
				}
			};
			
			
			if('PostInit' in eventsHandler) {
				uploader.bind('PostInit', eventsHandler.PostInit);
				// we should not delete events.
				// delete events['PostInit'];
			}
			
			
			uploader.init();
			$('#'+id).append('<div style="display:none;" id="'+hiddenId+'" ></div>');
			
			$.each(eventsHandler, function(key, callback) {
				// do not rebind post init
				if((key != 'PostInit') && (key != 'AsyncDelete')) {
					uploader.bind(key, callback);
				}
			});
			
			uploader.bind('FileUploaded', function(up, file, response) {
				var json = $.parseJSON( response.response );
				// for each uploaded file create the support hidden field
				createHiddenField(uploader, json, file, hiddenId, config);
			});
			
			uploader.bind('FilesRemoved', function(up, files) {
				$.each(files,  function(i, file){ 
					var filename = $('#h'+file.id).val();
					if(filename.search(/tmp:\/\//) == 0) {
						// we should handle delete only for temp files
						uploader.asyncDelete(file, filename);
					}
					$('#h'+file.id).remove();
					if($('#'+hiddenId+' input[type=hidden]').length == 0) {
						$('#'+hiddenId).append('<input type="hidden" id="sweeploadEmpty" name="'+config.realName+'" value="" />');
					}
					
				});
			});
			
			if(!!uploadedFiles) {
				// if we have files to show, we should present them as uploaded
				var jsFiles = [];
				$.each(uploadedFiles, function(idx, file) {
					var jsFile = new plupload.File(plupload.guid(), file.fileName, file.fileSize);
					jsFile.status = plupload.DONE;
					jsFile.percent = 100;
					jsFiles.push(jsFile);
				});
				uploader.trigger('FilesAdded', jsFiles);
				
				$.each(jsFiles, function(idx, jsFile){
					var response = { 'response' : '{"fileName":"'+jsFile.name+'", "status":true, "size":'+jsFile.size+'}', 'status' : true };
					uploader.trigger('FileUploaded', jsFile, response);
				});
				
				uploader.trigger('UploadComplete', jsFiles);
				uploader.refresh(); // not sure if this is needed
			}
			
			// is it linked to the ui ? probably
			if(!!config.auto) {
				uploader.bind('FilesAdded', function(up, file) {
					up.refresh();
					up.start();
				});
			}
			window['uploader_'+uploader.getId()] = uploader;
		});
	};
	
})(sweelix, jQuery);

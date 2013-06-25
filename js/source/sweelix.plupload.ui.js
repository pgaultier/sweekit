/**
 * File jquery.sweelix.plupload.ui.js
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

	function SweeploadBasicUI() {
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
						var config = getConfig();
						if('linkClass' in config) {
							element.addClass(config['linkClass']);						
						}
						if('store' in config) {
							element.data('store', config['store'])
						}
						if(data.image == false) {
							element.after($('<br/><span>'+data.path+'</span>'));
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

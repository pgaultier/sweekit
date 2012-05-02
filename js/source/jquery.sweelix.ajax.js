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
		'id': 'ajax',
		'major': 1,
		'minor': 4
	};
	var config = { };
	
	function createHiddenField(up, json, file, hiddenId, config) {
		if(json.status == true) {
			if(up.getMultiSelection() == false) {
				jQuery('#'+hiddenId+' input[type=hidden]').each(function(idx, el){
					var fileId = jQuery(el).attr('id');
					fileId = fileId.substring(1);
					jQuery.ajax.asyncDelete(up.getId(), fileId, function(id){ 
						jQuery('#'+id).remove();
					});
				});
			}
			jQuery('#'+hiddenId).append('<input type="hidden" id="h'+file.id+'" name="'+config.realName+'" value="'+json.fileName+'" />')
		}
	}
	JSONstring={
		compactOutput:false, 		
		includeProtos:false, 	
		includeFunctions: false,
		detectCirculars:true,
		restoreCirculars:true,
		make:function(arg,restore) {
			this.restore=restore;
			this.mem=[];this.pathMem=[];
			return this.toJsonStringArray(arg).join('');
		},
		toObject:function(x){
			if(!this.cleaner){
				try{this.cleaner=new RegExp('^("(\\\\.|[^"\\\\\\n\\r])*?"|[,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t])+?$')}
				catch(a){this.cleaner=/^(true|false|null|\[.*\]|\{.*\}|".*"|\d+|\d+\.\d+)$/}
			};
			if(!this.cleaner.test(x)){return {}};
			eval("this.myObj="+x);
			if(!this.restoreCirculars || !alert){return this.myObj};
			if(this.includeFunctions){
				var x=this.myObj;
				for(var i in x){if(typeof x[i]=="string" && !x[i].indexOf("JSONincludedFunc:")){
					x[i]=x[i].substring(17);
					eval("x[i]="+x[i])
				}}
			};
			this.restoreCode=[];
			this.make(this.myObj,true);
			var r=this.restoreCode.join(";")+";";
			eval('r=r.replace(/\\W([0-9]{1,})(\\W)/g,"[$1]$2").replace(/\\.\\;/g,";")');
			eval(r);
			return this.myObj
		},
		toJsonStringArray:function(arg, out) {
			if(!out){this.path=[]};
			out = out || [];
			var u; // undefined
			switch (typeof arg) {
			case 'object':
				this.lastObj=arg;
				if(this.detectCirculars){
					var m=this.mem; var n=this.pathMem;
					for(var i=0;i<m.length;i++){
						if(arg===m[i]){
							out.push('"JSONcircRef:'+n[i]+'"');return out
						}
					};
					m.push(arg); n.push(this.path.join("."));
				};
				if (arg) {
					if (arg.constructor == Array) {
						out.push('[');
						for (var i = 0; i < arg.length; ++i) {
							this.path.push(i);
							if (i > 0)
								out.push(',\n');
							this.toJsonStringArray(arg[i], out);
							this.path.pop();
						}
						out.push(']');
						return out;
					} else if (typeof arg.toString != 'undefined') {
						out.push('{');
						var first = true;
						for (var i in arg) {
							if(!this.includeProtos && arg[i]===arg.constructor.prototype[i]){continue};
							this.path.push(i);
							var curr = out.length; 
							if (!first)
								out.push(this.compactOutput?',':',\n');
							this.toJsonStringArray(i, out);
							out.push(':');                    
							this.toJsonStringArray(arg[i], out);
							if (out[out.length - 1] == u)
								out.splice(curr, out.length - curr);
							else
								first = false;
							this.path.pop();
						}
						out.push('}');
						return out;
					}
					return out;
				}
				out.push('null');
				return out;
			case 'unknown':
			case 'undefined':
			case 'function':
				if(!this.includeFunctions){out.push(u);return out};
				arg="JSONincludedFunc:"+arg;
				out.push('"');
				var a=['\\','\\\\','\n','\\n','\r','\\r','"','\\"'];arg+=""; 
				for(var i=0;i<8;i+=2){arg=arg.split(a[i]).join(a[i+1])};
				out.push(arg);
				out.push('"');
				return out;
			case 'string':
				if(this.restore && arg.indexOf("JSONcircRef:")==0){
					this.restoreCode.push('this.myObj.'+this.path.join(".")+"="+arg.split("JSONcircRef:").join("this.myObj."));
				};
				out.push('"');
				var a=['\n','\\n','\r','\\r','"','\\"'];
				arg+=""; for(var i=0;i<6;i+=2){arg=arg.split(a[i]).join(a[i+1])};
				out.push(arg);
				out.push('"');
				return out;
			default:
				out.push(String(arg));
				return out;
			}
		}
	};

	jQuery.sweelix.registerModule(module);

	jQuery.extend(jQuery.sweelix, {
		'ajax': {
			'init': function() {
				jQuery.extend(true, config, jQuery.sweelix.config(module.id));
				jQuery.ajax.asyncDelete = function(uploaderId, id, callback){
					var up = window['uploader_'+uploaderId];
					if(up.getDeleteUrl() != null) {
						var hiddenId = '#h'+id;
						jQuery.ajax({
							'url' : up.getDeleteUrl(),
							'data' : {'name':jQuery(hiddenId).val()},
							'success' : function(){
								jQuery(hiddenId).remove();
								if(typeof(callback) == 'function') {
									callback(id);
								}
							}
						});
					}
				};

				jQuery.fn.asyncUploadJqueryUI = function (config, events) {
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
					jQuery.extend(baseConfig, {'headers':{'X-Requested-With':'XMLHttpRequest'}});
					return this.each(function () {
						var id = jQuery(this).attr('id');
						var hiddenId = jQuery(this).attr('id')+'_hidden';
						baseConfig['browse_button'] = id;
						
						var uploader = $('#'+id).plupload(baseConfig).plupload('getUploader');
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
							uploader.bind('PostInit', jQuery.sweelix.plupload.postInit);
						}
						uploader.init();
						jQuery('#'+id).append('<div style="display:none;" id="'+hiddenId+'" ></div>');
						jQuery.each(events, function(key, callback) {
							uploader.bind(key, callback);
						});
						uploader.bind('FileUploaded', function(up, file, response) {
							var json = jQuery.parseJSON( response.response );
							createHiddenField(uploader, json, file, hiddenId, config);
						});
						if(!!config.auto) {
							uploader.bind('FilesAdded', function(up, file) {
								up.refresh();
								up.start();
							});
						}
						if(!!config.ui) {
							uploader.bind('FilesAdded', jQuery.sweelix.plupload.filesAdded);
							uploader.bind('FilesRemoved', jQuery.sweelix.plupload.filesRemoved);
							uploader.bind('UploadProgress', jQuery.sweelix.plupload.uploadProgress);
							uploader.bind('FileUploaded', jQuery.sweelix.plupload.fileUploaded);
							uploader.bind('Error', jQuery.sweelix.plupload.error);
						}
						if(!!uploadedFiles) {
							var jsFiles = [];
							jQuery.each(uploadedFiles, function(idx, file) {
								var jsFile = new plupload.File(plupload.guid(), file.fileName, file.fileSize);
								jsFiles.push(jsFile);
								createHiddenField(uploader, file, jsFile, hiddenId, config);
							});
							if(!!config.ui) {
								jQuery.sweelix.plupload.filesAdded(uploader, jsFiles);
								jQuery.each(jsFiles, function(idx, jsFile){
									var response = { 'response' : '{"fileName":"'+jsFile.name+'", "status":true, "size":'+jsFile.size+'}', 'status' : true };
									jQuery.sweelix.plupload.fileUploaded(uploader, jsFile, response);
								});
							}
							if('FilesAdded' in events) {
								events.FilesAdded(uploader, jsFiles);
							}
							if('FileUploaded' in events) {
								jQuery.each(jsFiles, function(idx, jsFile){
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

				jQuery.fn.asyncUpload = function (config, events) {
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
					jQuery.extend(baseConfig, {'headers':{'X-Requested-With':'XMLHttpRequest'}});
					return this.each(function () {
						var id = jQuery(this).attr('id');
						var hiddenId = jQuery(this).attr('id')+'_hidden';
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
							uploader.bind('PostInit', jQuery.sweelix.plupload.postInit);
						}
						uploader.init();
						jQuery('#'+id).append('<div style="display:none;" id="'+hiddenId+'" ></div>');
						jQuery.each(events, function(key, callback) {
							uploader.bind(key, callback);
						});
						uploader.bind('FileUploaded', function(up, file, response) {
							var json = jQuery.parseJSON( response.response );
							createHiddenField(uploader, json, file, hiddenId, config);
						});
						if(!!config.auto) {
							uploader.bind('FilesAdded', function(up, file) {
								up.refresh();
								up.start();
							});
						}
						if(!!config.ui) {
							uploader.bind('FilesAdded', jQuery.sweelix.plupload.filesAdded);
							uploader.bind('FilesRemoved', jQuery.sweelix.plupload.filesRemoved);
							uploader.bind('UploadProgress', jQuery.sweelix.plupload.uploadProgress);
							uploader.bind('FileUploaded', jQuery.sweelix.plupload.fileUploaded);
							uploader.bind('Error', jQuery.sweelix.plupload.error);
						}
						if(!!uploadedFiles) {
							var jsFiles = [];
							jQuery.each(uploadedFiles, function(idx, file) {
								var jsFile = new plupload.File(plupload.guid(), file.fileName, file.fileSize);
								jsFiles.push(jsFile);
								createHiddenField(uploader, file, jsFile, hiddenId, config);
							});
							if(!!config.ui) {
								jQuery.sweelix.plupload.filesAdded(uploader, jsFiles);
								jQuery.each(jsFiles, function(idx, jsFile){
									var response = { 'response' : '{"fileName":"'+jsFile.name+'", "status":true, "size":'+jsFile.size+'}', 'status' : true };
									jQuery.sweelix.plupload.fileUploaded(uploader, jsFile, response);
								});
							}
							if('FilesAdded' in events) {
								events.FilesAdded(uploader, jsFiles);
							}
							if('FileUploaded' in events) {
								jQuery.each(jsFiles, function(idx, jsFile){
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

				/**
				 * Handle ajax update for form. Allow classic renderPartial update or direct javascript eval
				 * 
				 * @param string targetSelector where to update element
				 */
				jQuery.fn.ajaxSubmitHandler = function () {
					jQuery.sweelix.info('jQuery(%s).ajaxSubmitHandler()', this.selector);
					return this.each(function () {
						jQuery(this).bind('submit', function(evt) {
							evt.preventDefault();
							jQuery(this).trigger('beforeAjax');
							var targetUrl = jQuery(this).attr('action');
							if(typeof(targetUrl) == 'undefined') {
								targetUrl = jQuery(location).attr('href');
							}
							jQuery.ajax({
								headers: { 
									'Accept' : 'application/javascript;q=0.9,text/html;q=0.8,*/*;q=0.5'
								},
								'data':jQuery(this).serialize(),
								'url':targetUrl,
								'type':'POST',
								'context':this,
								'success':function(data, status, xhr){
								},
								'complete':function(xhr, status, data) {
									switch(xhr.getResponseHeader('Content-Type')) {
										case 'application/javascript' :
											break;
										case 'text/html' :
										default :
											jQuery(this).html(xhr.responseText);
											break;
									}
									jQuery(this).trigger('afterAjax');
								}
							});
						});
					});
				};
				
				/**
				 * Refresh part
				 * 
				 * @param mixed params array('targetUrl' => url, 'data' => 'post data if needed', 'mode' => 'can be replace or empty', 'targetSelector' => 'where to render content')
				 */
				jQuery.sweelix.register('ajaxRefreshHandler',function(params) {
					jQuery.sweelix.info('sweelix.ajaxRefreshHandler()', this.selector);
					jQuery(params.targetSelector).trigger('beforeAjax');
					jQuery.ajax({
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
										jQuery(params.targetSelector).replaceWith(data);
									} else {
										jQuery(params.targetSelector).html(data);
									}
									break;
							}
							jQuery(params.targetSelector).trigger('afterAjax');
						}
					});
				});
				jQuery.sweelix.info('sweelix.%s : init module version (%d.%d)', module.id, module.major, module.minor);
			}
		}
	});
})(jQuery);

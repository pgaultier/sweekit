<?php
/**
 * Sweeml.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.8.0
 * @link      http://www.sweelix.net
 * @category  extensions
 * @package   Sweeml
 */

Yii::import('ext.sweelix.sweekit.validators.SwFileValidator');

/**
 * This Sweeml class override CHtml class to
 * allow the use of javascript elements
 *
 * samples :
 * Methods to generate code to use as url (href="")
 * <code>
 * 	$urlRaiseEvent = Sweeml::raiseEventUrl($eventName, $parameters, $context);
 * 	$urlRaiseEvent = Sweeml::raiseRedirectUrl($url);
 * 	$urlRaiseEvent = Sweeml::raiseOpenShadowboxUrl($url, $shadowBoxOptions);
 * 	$urlRaiseEvent = Sweeml::raiseCloseShadowboxUrl();
 * </code>
 *
 * Methods to generate code to use in script code
 * <code>
 * 	$jsRaiseEvent = Sweeml::raiseEvent($eventName, $parameters, $context);
 * 	$jsRaiseEvent = Sweeml::raiseRedirect($url);
 * 	$jsRaiseEvent = Sweeml::raiseOpenShadowbox($url, $shadowBoxOptions);
 * 	$jsRaiseEvent = Sweeml::raiseCloseShadowbox();
 * </code>
 *
 * Method to register an event using the clientScript
 * <code>
 * 	Sweeml::registerEvent($eventName, $action, $context);
 * </code>
 *
 * Method to generate js code to register an event manually
 * <code>
 * 	$jsRaiseEvent = Sweeml::registerEventScript($eventName, $action, $context);
 * </code>
 *
 * Method to generate async file upload
 * <code>
 * 	Sweeml::asyncFileUpload($model, $attribute, $options);
 * </code>
 *
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.8.0
 * @link      http://www.sweelix.net
 * @category  extensions
 * @package   Sweeml
 * @since     1.1
 */
class Sweeml extends CHtml {
	/**
	 * Generates the data suitable for list-based HTML elements.
	 * The generated data can be used in {@link dropDownList}, {@link listBox}, {@link checkBoxList},
	 * {@link radioButtonList}, and their active-versions (such as {@link activeDropDownList}).
	 * Note, this method does not HTML-encode the generated data. You may call {@link encodeArray} to
	 * encode it if needed.
	 * Please refer to the {@link value} method on how to specify value field, text field and group field.
	 * @param CActiveDataProvider $models a list of model objects. This parameter
	 * can also be an array of associative arrays (e.g. results of {@link CDbCommand::queryAll}).
	 * @param string $valueField the attribute name for list option values
	 * @param string $textField the attribute name for list option texts
	 * @param string $groupField the attribute name for list option group names. If empty, no group will be generated.
	 * @return array the list data that can be used in {@link dropDownList}, {@link listBox}, etc.
	 */
	public static function listDataFromActiveDataProvider($activeDataProvider,$valueField,$textField,$groupField='')
	{
		$listData=array();
		if($groupField==='')
		{
			foreach($activeDataProvider->getData() as $model)
			{
				$value=self::value($model,$valueField);
				$text=self::value($model,$textField);
				$listData[$value]=$text;
			}
		}
		else
		{
			foreach($activeDataProvider->getData() as $model)
			{
				$group=self::value($model,$groupField);
				$value=self::value($model,$valueField);
				$text=self::value($model,$textField);
				$listData[$group][$value]=$text;
			}
		}
		return $listData;
	}
	/**
	 * Create an asynchronous file upload. This kind of
	 * file upload allow the use of fully ajaxed forms
	 * supported htmlOptions are :
	 * <code>
	 * $htmlOptions = array(
	 * 		'tag' => 'button', // can be any tag which can have html inside (avoid input) button is the default html tag used. in case of button, "type" is defaulted to "button"
	 *  	'content' => Yii::t('sweelix', 'Browse ...'),
	 *  	// ... // all classic html options
	 *  	'config' => array(
	 *  		'runtimes' => 'html5, html4', // can be : html5, html4, flash, browserplus, gears, silverlight
	 *  		'dropElement' => 'id_zone', // String with the ID of the element that you want to be able to drop files into this is only used by some runtimes that support it
	 *  		'ui' => false, // true to display default ui system (override events, ...) or object to handle events
	 *  					   // js:new UploadManager() to handle all plupload events
	 *  					   // one special event has been added :AsyncDelete is for callback when delete is asked
	 *  		'multiSelection' => false, // allow multifile upload
	 *  		'url' => '...', // default upload url for temporary upload
	 *  		'urlDelete' => '...', // default delete url for temporary upload
	 *  	),
	 * );
	 * </code>
	 *
	 * @param CModel $model       original model used
	 * @param string $attribute   attribute to draw
	 * @param array  $htmlOptions html options
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function asyncFileUpload($name, $htmlOptions=array(), $value=null) {
		if($value === null) {
			$value = SwUploadedFile::getInstancesByName($name);
		}

		$htmlOptions['name'] = $name;
		$htmlOptions['id']=self::getIdByName($name);
		$config = self::prepareAsyncFileUpload($htmlOptions);

		return self::renderAsyncFileUpload($value, $htmlOptions, $config);
	}

	/**
	 * Create an asynchronous file upload take care this
	 * kind of file upload does not gracefully downgrade
	 *
	 * @param CModel $model       original model used
	 * @param string $attribute   attribute to draw
	 * @param array  $htmlOptions html options
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function activeAsyncFileUpload($model, $attribute, $htmlOptions=array()) {
		self::resolveNameID($model,$attribute,$htmlOptions);
		$filters = array();
		foreach($model->getValidators($attribute) as $validator) {
			if($validator instanceof CFileValidator) {
				if(is_string($validator->types) === true) {
					$filters[] = array('extensions' => str_replace(' ', '',$validator->types));
				}elseif(is_array($validator->types) === true) {
					$filters[] = array('extensions' => implode(',',$validator->types));
				}
				if(($validator->maxFiles == 0) || ($validator->maxFiles > 1)) {
					$htmlOptions['config']['multiSelection'] = true;
				} else {
					$htmlOptions['config']['multiSelection'] = false;
				}
			}
		}
		// override data store to allow js to be aware of the attribute
		$htmlOptions['config']['eventHandlerConfig']['store'] = $attribute;
		if(count($filters) > 0) {
			if(isset($htmlOptions['config']['filters']) === true) {
				$htmlOptions['config']['filters'] = CMap::mergeArray($filters, $htmlOptions['config']['filters']);
			} else {
				$htmlOptions['config']['filters'] = $filters;
			}
		}

		Yii::import('ext.sweekit.web.SwUploadedFile');

		$value = SwUploadedFile::getInstances($model, $attribute);
		$fileList = $model->$attribute;
		if((is_array($fileList) === false) && (empty($fileList) === false)) {
			$fileList = array($fileList);
		} elseif(is_array($fileList) === false) {
			$fileList = null;
		}

		if($fileList !== null) {
			$resourcePath = Yii::getPathOfAlias(isset($htmlOptions['resourcesPath'])?$htmlOptions['resourcesPath']:'webroot');
			if(isset($htmlOptions['resourcesPath']) === true) { unset($htmlOptions['resourcesPath']); }
			foreach($fileList as $element) {
				if((strncasecmp('tmp://', $element, 6) !== 0) && (empty($element) === false)) {
					$realFile = $resourcePath.DIRECTORY_SEPARATOR.$element;
					if(file_exists($realFile) === true) {
						$fileInfo = pathinfo($realFile);
						$value[] = new SwUploadedFile($fileInfo['basename'], $realFile, $fileInfo['extension'], filesize($realFile), $model, $attribute);
					}
				}
			}
		}
		$config = self::prepareAsyncFileUpload($htmlOptions);

		if($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		return self::renderAsyncFileUpload($value, $htmlOptions, $config);
	}

	/**
	 * render the asyncfile element using base data
	 *
	 * @param array $values         already uploaded files
	 * @param array $htmlOptions    element htmlOptions
	 * @param array $config         configuration parameters
	 * @param array $attachedEvents events attached to the asyncfile element
	 *
	 * @return string
	 * @since  1.1.0
	 */
	protected static function renderAsyncFileUpload($values, $htmlOptions, $config) {
		if(is_array($values) == true) {
			$uploadedFiles = null;
			foreach($values as $addedFile) {
				if($addedFile instanceof SwUploadedFile) {
					$uploadedFiles[] = array('fileName' => $addedFile->getName(), 'fileSize' => $addedFile->getSize(), 'status' => true);
				}
			}
			if($uploadedFiles !== null) {
				$config['uploadedFiles'] = $uploadedFiles;
			}
		} elseif($values instanceof SwUploadedFile) {
			$config['uploadedFiles'][] = array('fileName' => $addedFile->getName(), 'fileSize' => $addedFile->getSize(), 'status' => true);
		}
		unset($htmlOptions['name']);
		if(isset($htmlOptions['tag']) == true) {
			$tag = $htmlOptions['tag'];
			unset($htmlOptions['tag']);
		} else {
			$tag = 'button';
			if(isset($htmlOptions['type']) == false) {
				$htmlOptions['type'] = 'button';
			}
		}
		if(isset($htmlOptions['content']) == true) {
			$content = $htmlOptions['content'];
			unset($htmlOptions['content']);
		} else {
			$content = Yii::t('sweelix', 'Browse ...');
		}

		$js = 'jQuery(\'#'.$htmlOptions['id'].'\').asyncUpload('.CJavaScript::encode($config).');';
		unset($htmlOptions['uploadOptions']);
		unset($htmlOptions['value']);

		$htmlTag = self::tag($tag, $htmlOptions, $content);
		if(Yii::app()->getRequest()->isAjaxRequest === false) {
			Yii::app()->clientScript->registerScript($htmlOptions['id'], $js);
		} else {
			$htmlTag = $htmlTag.' '.self::script($js);
		}
		return $htmlTag;
	}
	/**
	 * Rework htmlOptions to prepare asyncfile upload data and return
	 * array(configArray, eventsArray)
	 *
	 * @param array &$htmlOptions htmlOptions used
	 *
	 * @return array
	 * @since  1.1.0
	 */
	protected static function prepareAsyncFileUpload(&$htmlOptions) {
		$config = array(
			'runtimes' => 'html5, html4', // default to html5 / html4
			'dropElement' => $htmlOptions['id'].'_zone',
			'dropText' => Yii::t('sweelix', 'Drop files here'),
			'ui' => false,
			'multiSelection' => false,
			'url' => array('asyncUpload', 'id'=>$htmlOptions['id'], 'key' => Yii::app()->getSession()->getSessionId()),
			'urlDelete' => array('asyncDelete', 'id'=>$htmlOptions['id'], 'key' => Yii::app()->getSession()->getSessionId()),
			'urlPreview' => null,
		);
		if(isset($htmlOptions['config']) == true) {
			if(isset($htmlOptions['config']['urlPreview']) && is_array($htmlOptions['config']['urlPreview']) === true) {
				$htmlOptions['config']['urlPreview'] = array_merge($htmlOptions['config']['urlPreview'], array('id'=>$htmlOptions['id'], 'key' => Yii::app()->getSession()->getSessionId()));
			}
			if(isset($htmlOptions['config']['urlDelete']) && is_array($htmlOptions['config']['urlDelete']) === true) {
				$htmlOptions['config']['urlDelete'] = array_merge($htmlOptions['config']['urlDelete'], array('id'=>$htmlOptions['id'], 'key' => Yii::app()->getSession()->getSessionId()));
			}
			if(isset($htmlOptions['config']['url']) && is_array($htmlOptions['config']['url']) === true) {
				$htmlOptions['config']['url'] = array_merge($htmlOptions['config']['url'], array('id'=>$htmlOptions['id'], 'key' => Yii::app()->getSession()->getSessionId()));
			}

			$config = CMap::mergeArray($config, $htmlOptions['config']);
			foreach(array('url', 'urlDelete', 'urlPreview') as $rawUrl) {
				if(isset($config[$rawUrl]) === true) {
					$config[$rawUrl] = self::normalizeUrl($config[$rawUrl]);
				}
			}
			unset($htmlOptions['config']);
		}
		$config['realName'] = $htmlOptions['name'];
		if($config['multiSelection'] == true) {
			$config['realName'] .= '[]';
		}
		$runtimes = explode(',', str_replace(' ','', $config['runtimes']));
		if(Yii::app()->getRequest()->isAjaxRequest === false) {
			// we have to register the scripts in the window
			foreach($runtimes as $runtime) {
				Yii::app()->getClientScript()->registerSweelixScript('plupload.'.$runtime);
				if($runtime == 'flash') {
					$config['flashSwfUrl'] = Yii::app()->getClientScript()->getSweelixAssetUrl().'/plupload/plupload.flash.swf';
				}
				if($runtime == 'silverlight') {
					$config['silverlightXapUrl'] = Yii::app()->getClientScript()->getSweelixAssetUrl().'/plupload/plupload.silverlight.xap';
				}
			}
			Yii::app()->getClientScript()->registerSweelixScript('sweepload');
			if($config['ui'] === true) {
				Yii::app()->getClientScript()->registerSweelixScript('sweepload.ui');
			}
		}
		return $config;
	}

	private static $_ajaxedFormCount = 0;

	/**
	 * Render everything to ajax one specific form
	 *
	 * @param mixed  $action
	 * @param string $method
	 * @param array  $htmlOptions
	 *
	 * @return string
	 * @sinces XXX
	 */
	public static function beginAjaxForm($action='',$method='post',$htmlOptions=array()) {
		if(isset($htmlOptions['id']) === false) {
			$id = 'ajaxedForm';
			if(self::$_ajaxedFormCount > 0) {
				$id .= self::$_ajaxedFormCount;
			}
			$htmlOptions['id'] = $id;
			self::$_ajaxedFormCount++;
		}
		self::ajaxSubmitHandler('#'.$htmlOptions['id']);
		return parent::beginForm($action, $method, $htmlOptions);
	}

	/**
	 * Generate a shadowbox open script using raiseevents
	 *
	 * @param mixed  $url              url information will be normalized
	 * @param array  $shadowBoxOptions options to pass to shadowbox as described in documentation
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseOpenShadowbox($url='#', $shadowBoxOptions=array()) {
		Yii::app()->getClientScript()->registerSweelixScript('shadowbox');
		if(!isset($shadowBoxOptions['content'])) {
			$shadowBoxOptions['content']=self::normalizeUrl($url);
		}
		if(!isset($shadowBoxOptions['player'])) {
			$shadowBoxOptions['player']='iframe';
		}
		return self::raiseEvent('shadowboxOpen', $shadowBoxOptions);
	}

	/**
	 * Generate a shadowbox close script using raiseevents
	 *
	 * @param string $eventName name of the event to raise. Usefull if multiple events are available
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseCloseShadowbox() {
		Yii::app()->getClientScript()->registerSweelixScript('shadowbox');
		return self::raiseEvent('shadowboxClose');
	}

	/**
	 * Generate a notice open script using raiseevents
	 *
	 * @param array  $noticeOptions options to pass to notice as described in documentation
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseShowNotice($noticeOptions=array()) {
		Yii::app()->getClientScript()->registerSweelixScript('notice');
		return self::raiseEvent('showNotice', $noticeOptions);
	}

	/**
	 * Raise redirect js event
	 *
	 * @param array   $url   url in yii format
	 * @param integer $timer delay in second before executing redirect
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseRedirect($url, $timer=null) {
		if($timer !== null) {
			return self::raiseEvent('redirect', array('url' => self::normalizeUrl($url), 'timer' => $timer));
		} else {
			return self::raiseEvent('redirect', self::normalizeUrl($url));
		}
	}

	/**
	 * Raise redirect js event through url
	 *
	 * @param array $url url in yii format
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseRedirectUrl($url) {
		return 'javascript:'.self::raiseRedirect($url);
	}

	/**
	 * Generate a shadowbox open script ready to set in link (url)
	 * using raiseevents
	 *
	 * @param mixed  $url              url information will be normalized
	 * @param array  $shadowBoxOptions options to pass to shadowbox as described in documentation
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseOpenShadowboxUrl($url='#', $shadowBoxOptions=array()) {
		return 'javascript:'.self::raiseOpenShadowbox($url, $shadowBoxOptions);
	}

	/**
	 * Generate a shadowbox close script ready to set in link (url)
	 * using raiseevents
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseCloseShadowboxUrl() {
		return 'javascript:'.self::raiseCloseShadowbox();
	}

	/**
	 * Generate a notice open script to set in link (url)
	 * using raiseevents
	 *
	 * @param array  $noticeOptions options to pass to notice as described in documentation
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseShowNoticeUrl($noticeOptions=array()) {
		return 'javascript:'.self::raiseShowNotice($noticeOptions);
	}

	/**
	 * Generate a raise event script.
	 *
	 * @param string $eventName  name of the event to raise
	 * @param array  $parameters parameters to pass to the event manager
	 * @param string $context    context if needed, else will be in global context
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseEvent($eventName, $parameters=array(), $context=null) {
		Yii::app()->getClientScript()->registerSweelixScript('callback');
		if($context === null) {
			return 'sweelix.raise(\''.$eventName.'\', '.CJavaScript::encode($parameters).');';
		} else {
			return 'sweelix.raiseNamed(\''.$context.'\', \''.$eventName.'\', '.CJavaScript::encode($parameters).');';
		}
	}

	/**
	 * Register and attach the ajaxSubmitHandler
	 *
	 * @param string $target dom target element
	 */
	public static function ajaxSubmitHandler($target) {
		$scriptName = 'ajaxSubmitHandler'.preg_replace('/[^a-z0-9]/','', $target);
		Yii::app()->getClientScript()->registerScript($scriptName, self::ajaxSubmitHandlerScript($target), CClientScript::POS_READY);
	}

	/**
	 * Register and attach the ajaxSubmitHandler
	 *
	 * @param string $target dom target element
	 */
	public static function ajaxSubmitHandlerScript($target) {
		Yii::app()->getClientScript()->registerSweelixScript('ajax');
		return 'jQuery(\''.$target.'\').ajaxSubmitHandler();';
	}

	/**
	 * Raise refresh handler in js
	 *
	 * @param string $target target element
	 * @param array  $url    url in yii format
	 * @param array  $data   data to pass
	 * @param string $mode   replacement mode can be replace or update
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseAjaxRefresh($target, $url, $data=null, $mode=null) {
		Yii::app()->getClientScript()->registerSweelixScript('ajax');
		return self::raiseEvent('ajaxRefreshHandler', array('targetUrl' => self::normalizeUrl($url), 'data'=>$data, 'targetSelector' => $target, 'mode'=>$mode));
	}
	/**
	 * Raise refresh handler in url format
	 *
	 * @param string $target target element
	 * @param array  $url    url in yii format
	 * @param array  $data   data to pass
	 * @param string $mode   replacement mode can be replace or update
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseAjaxRefreshUrl($target, $url, $data=null, $mode=null) {
		return 'javascript:'.self::raiseAjaxRefresh($target, $url, $data, $mode);
	}

	/**
	 * Generate a raise event url to use in links, ...
	 *
	 * @param string $eventName  name of the event to raise
	 * @param array  $parameters parameters to pass to the event manager
	 * @param string $context    context if needed, else will be in global context
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function raiseEventUrl($eventName, $parameters=array(), $context=null) {
		return 'javascript:'.self::raiseEvent($eventName, $parameters, $context);
	}

	/**
	 * Generate a raise event script. This script can be registered manually
	 *
	 * @param string $eventName name of the event to raise
	 * @param array  $action    action to execute when event is raised, this is pure javascript code
	 * @param string $context   context if needed, else will be in global context
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public static function registerEventScript($eventName, $action, $context=null) {
		Yii::app()->getClientScript()->registerSweelixScript('callback');
		if($context === null) {
			return 'sweelix.register(\''.$eventName.'\', '.CJavaScript::encode($action).');';
		} else {
			return 'sweelix.register(\''.$context.'\', \''.$eventName.'\', '.CJavaScript::encode($action).');';
		}
	}
	/**
	 * Register a new javascript event handler
	 *
	 * @param string $eventName name of the event to register
	 * @param string $action    action to execute when event is raised, this is pure javascript code
	 * @param string $context   context if needed, else will be in registered in global context
	 *
	 * @return void
	 * @since  1.1.0
	 */
	public static function registerEvent($eventName, $action, $context=null) {
		$js = self::registerEventScript($eventName, $action, $context);
		if($context === null) {
			Yii::app()->getClientScript()->registerScript($eventName, $js, CClientScript::POS_READY);
		} else {
			Yii::app()->getClientScript()->registerScript($context.'-'.$eventName, $js, CClientScript::POS_READY);
		}
	}
}
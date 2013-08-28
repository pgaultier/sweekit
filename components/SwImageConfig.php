<?php
/**
 * File SwImageConfig.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 */

Yii::import('ext.sweekit.web.SwImage');

/**
 * Class SwImageConfig
 *
 * This module allow automatic configuration for class SwCacheImage.
 * Once module is configured , SwCacheImage inherit of basic properties
 * such as
 *
 *  - cachePath
 *  - cachingMode
 *  - urlSeparator
 *  - quality
 *
 * id of the module should be set to "image". If not, we will attempt to find
 * correct module.
 *
 * <code>
 * 	'components' => array(
 * 		...
 * 		'image' => array(
 * 			'class'=>'sweelix.wrappers.yii.web.SwImageConfig',
 * 			'quality'=>80,
 * 			'cachingMode'=>'performance',
 * 			'urlSeparator'=>'/',
 * 			'cachePath'=>'cache',
 * 			'errorImage'=>'error.jpg',
 * 		),
 * 		...
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 * @since     1.11.0
 */
class SwImageConfig extends CApplicationComponent {
	/**
	 * @var boolean define status of the module
	 */
	private $_initialized = false;
	/**
	 * @var integer define caching mode @see SwImage for further details
	 */
	private $_cachingMode = null;
	/**
	 * Caching mode setter @see SwImage::cachingMode for further details
	 *
	 * @param integer $mode can be performance, normal or debug
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setCachingMode($mode) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'ImageConfig, cachingMode can be defined only in configuration'));
		}
		$this->_cachingMode = $mode;
	}
	/**
	 * Caching mode getter @see SwImage for further details
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getCachingMode() {
		return $this->_cachingMode;
	}
	/**
	 * @var string this separator is used to build Urls
	 */
	private $_urlSeparator = '/';
	/**
	 * Url separator setter @see SwImage::urlSeparator for further details
	 *
	 * @param string $urlSeparator separator used to build Urls
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setUrlSeparator($urlSeparator) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'ImageConfig, urlSeparator can be defined only in configuration'));
		}
		$this->_urlSeparator = $urlSeparator;
	}
	/**
	 * Url separator getter @see SwImage::urlSeparator for further details
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function getUrlSeparator() {
		return $this->_urlSeparator;
	}
	/**
	 * @var string define default cache path
	 */
	private $_cachePath = 'cache';
	/**
	 * Cache path setter @see SwImage::cachePath for further details
	 *
	 * @param string $cachePath real path (not namespace path)
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setCachePath($cachePath) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'ImageConfig, cachePath can be defined only in configuration'));
		}
		$this->_cachePath = $cachePath;
	}
	/**
	 * Cache path getter @see SwImage::cachePath for further details
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function getCachePath() {
		return $this->_cachePath;
	}
	/**
	 * @var string this image is used when original image cannot be found
	 */
	private $_errorImage = 'error.jpg';
	/**
	 * Error image setter @see SwImage::errorImage for further details
	 *
	 * @param string $errorImage error image name
	 *
	 * @return void
	 * @since  1.2.0
	 */
	public function setErrorImage($errorImage) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'ImageConfig, errorImage can be defined only in configuration'));
		}
		$this->_errorImage = $errorImage;
	}
	/**
	 *  Error image getter @see SwImage::errorImage for further details
	 *
	 * @return string
	 * @since  1.2.0
	 */
	public function getErrorImage() {
		return $this->_errorImage;
	}
	/**
	 * @var integer define the quality used for the rendering
	 */
	private $_quality = 90;
	/**
	 * Quality setter @see SwImage::setQuality() for further details
	 *
	 * @param integer $quality image quality default to 90
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function setQuality($quality) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'ImageConfig, quality can be defined only in configuration'));
		}
		$this->_quality = $quality;
	}
	/**
	 * Cache path getter @see SwImage::cachePath for further details
	 *
	 * @return integer
	 * @since  1.0.0
	 */
	public function getQuality() {
		return $this->_quality;
	}

	/**
	 * Init module with parameters @see CApplicationComponent::init()
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function init() {
    	$this->attachBehaviors($this->behaviors);
		if ((is_writable($this->_cachePath)===false) || (is_dir($this->_cachePath)===false)) {
			throw new CException(Yii::t('sweelix', 'ImageConfig, cachePath is invalid'));
		}
		$this->setCachingMode(SwImage::MODE_NORMAL);
		$this->_initialized = true;
	}
}
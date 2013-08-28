<?php
/**
 * File SwCacheImage.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.11.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 */

Yii::import('ext.sweekit.web.SwImage');

/**
 * Class SwCacheImage wraps @see SwImage and
 * Yii into one class to inherit Yii config
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.11.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 * @since     1.11.0
 */
class SwCacheImage extends SwImage {
	/**
	 * Constructor, create an image object. This object will
	 * allow basic image manipulation
	 *
	 * @param string  $fileImage image name with path
	 * @param integer $quality   quality, default is @see SwImage::_quality
	 * @param integer $ratio     ratio, default is @see SwImage::_ratio
	 *
	 * @return SwCacheImage
	 */
	public function __construct($fileImage, $quality=null, $ratio=null) {
		Yii::trace(Yii::t('sweelix', '{class} create image', array('{class}'=>get_class($this))), 'ext.sweekit.web');
		$module = Yii::app()->getComponent('image');
		if($module===null) {
			Yii::log(Yii::t('sweelix', '{object} has not been defined', array('{object}'=>'SwImageConfig')), CLogger::LEVEL_ERROR, 'ext.sweekit.web');
			throw new CException(Yii::t('sweelix', 'ImageConfig, component has not been defined'));
		}
		$this->cachePath = $module->getCachePath();
		$this->cachingMode = $module->getCachingMode();
		$this->setQuality($module->getQuality());
		self::$urlSeparator = $module->getUrlSeparator();
		self::$errorImage = $module->getErrorImage();
		parent::__construct($fileImage, $quality, $ratio);
	}

	/**
	 * Create an instance of SwCacheImage with correct parameters
	 * calls original constructor @see SwCacheImage::__construct()
	 *
	 * @param string  $fileImage image name with path
	 * @param integer $quality   quality, default is @see SwImage::_quality
	 * @param integer $ratio     ratio, default is @see SwImage::_ratio
	 *
	 * @return SwCacheImage
	 */
	public static function create($fileImage, $quality=null, $ratio=null) {
		Yii::trace(Yii::t('sweelix', '{class} get instance of image', array('{class}'=>__CLASS__)), 'ext.sweekit.web');
		$class = __CLASS__;
		return new $class($fileImage, $quality, $ratio);
	}
}
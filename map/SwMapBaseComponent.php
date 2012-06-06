<?php
/**
 * SwMapBaseComponent.php
 *
 * PHP version 5.2+
 *
 * Google map base component
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  map
 * @package   Sweeml.map
 */

/**
 * This SwMapBaseComponent class handle tbasic logic for
 * google map calls
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  map
 * @package   Sweeml.map
 * @since     1.10.0
 */
class SwMapBaseComponent extends CComponent {
	public $componentName = 'sweekitmap';
	/**
	 * Get current google map application component
	 *
	 * @return SwMap
	 * @since  1.10.0
	 */
	public function getMap() {
		return Yii::app()->getComponent($this->componentName);
	}
	private $_language;
	/**
	 * Define language to use
	 *
	 * @param string $language language to use
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setLanguage($language) {
		$this->_language = $language;
	}
	/**
	 * Return language in use
	 * @return string
	 */
	public function getLanguage() {
		if($this->_language === null) {
			return $this->getMap()->getLanguage();
		} else {
			return $this->_language;
		}
	}

	private $_outputFormat;
	/**
	 * Define output format to use
	 *
	 * @param string $outputFormat format to use : json/xml
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setOutputFormat($outputFormat) {
		$this->_outputFormat = $outputFormat;
	}
	/**
	 * Return language in use
	 * @return string
	 */
	public function getOutputFormat() {
		if($this->_outputFormat === null) {
			return $this->getMap()->getOutputFormat();
		} else {
			return $this->_outputFormat;
		}
	}
	private $_sensor;
	/**
	 * Define if we use the GPS or not
	 *
	 * @param boolean $status true to use the sensor
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setSensor($status) {
		if($status === true) {
			$this->_sensor = 'true';
		} else {
			$this->_sensor = 'false';
		}
	}
	/**
	 * Return if the sensor is in use
	 * @return string
	 */
	public function getSensor($asString = true) {
		if($this->_sensor === null) {
			return $this->getMap()->getSensor($asString);
		} else {
			if($asString === false) {
				return CPropertyValue::ensureBoolean($this->_sensor);
			} else {
				return $this->_sensor;
			}
		}

	}
}
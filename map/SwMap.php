<?php
/**
 * SwMap.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  map
 * @package   Sweeml.map
 */

Yii::import('ext.sweekit.components.SwCurlRequest');

/**
 * SwMap is an app component which allow configuration for google map engine
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  map
 * @package   Sweeml.map
 * @since     1.10.0
 *
 * @property boolean $sensor       sensor status
 * @property string  $outputFormat webservice format
 * @property string  $language     language in use
 * @property integer $cacheExpire  duration before cache expire in seconds
 */
class SwMap extends CApplicationComponent {
	private $_sensor='false';
	private $_outputFormat = 'json';
	private $_currentRequest;
	private $_language;
	private $_cacheExpire=600;

	/**
	 * @var string base API Url
	 */
	public $baseUrl = 'http://maps.googleapis.com/maps/api/';

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
		if($asString === false) {
			return CPropertyValue::ensureBoolean($this->_sensor);
		} else {
			return $this->_sensor;
		}
	}

	/**
	 * Define the cache duration to use. Speed up app and
	 * avoid unecessary google calls
	 *
	 * @param integer $duration cache duration in seconds
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setCacheExpire($duration) {
		$this->_cacheExpire = $duration;
	}

	/**
	 * Get the cache duration in seconds
	 *
	 * @return integer
	 * @since  1.10.0
	 */
	public function getCacheExpire() {
		return $this->_cacheExpire;
	}

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
		return $this->_language;
	}

	/**
	 * Define output format to use
	 *
	 * @param string $type wanted format
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setOutputFormat($type) {
		if(in_array($type, array('json', 'xml')) === true) {
			$this->_outputFormat = $type;
		}
	}
	/**
	 * Return generic ouput format to use (json/xml)
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getOutputFormat() {
		return $this->_outputFormat;
	}

	/**
	 * Run webservice request. The cache is used in case request has
	 * already been fetched
	 *
	 * @param array $parameters parameters needed byt the map service
	 *
	 * @return SwCurlResponse
	 * @since  1.10.0
	 */
	public function execute($parameters=null) {
		$requestHash = md5(serialize($parameters));
		if(isset($this->_currentRequest[$requestHash]) === false) {
			$result = false;
			if(Yii::app()->getCache() !== null) {
				$result = Yii::app()->getCache()->get($requestHash);
			}
			if($result === false) {
				$serviceUrl = $this->baseUrl;
				if(isset($parameters['service']) === true) {
					$serviceUrl .= $parameters['service'].'/';
					unset($parameters['service']);
				}
				if(isset($parameters['outputFormat']) === true) {
					$serviceUrl .= $parameters['outputFormat'];
					unset($parameters['outputFormat']);
				} else {
					$serviceUrl .= $this->getOutputFormat();
				}
				$request = Yii::createComponent('ext.sweekit.components.SwCurlRequest', $serviceUrl);
				$request->setUrlParameters($parameters);

				$this->_currentRequest[$requestHash] = $request->execute();
				if(Yii::app()->getCache() !== null) {
					$result = Yii::app()->getCache()->set($requestHash, $this->_currentRequest[$requestHash], $this->getCacheDuration());
				}
			} else {
				$this->_currentRequest[$requestHash] = $result;
			}
		}
		return $this->_currentRequest[$requestHash];
	}



}
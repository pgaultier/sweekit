<?php
/**
 * SwMapCoordinates.php
 *
 * PHP version 5.2+
 *
 * Google map coordinates
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  map
 * @package   Sweeml.map
 */

Yii::import('ext.sweekit.map.SwMapBaseComponent');

/**
 * This SwMapCoordinates class handle the logic for
 * google map coordinates
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
class SwMapCoordinates extends SwMapBaseComponent {
	public $latitude;
	public $longitude;

	/**
	 * SwMapCoordinates constructor
	 *
	 * @param float $latitude  latitude (xxx.xxxxxxx)
	 * @param float $longitude longitude (xxx.xxxxxxx)
	 *
	 * @return SwMapCoordinates
	 * @since  1.10.0
	 */
	public function __construct($latitude=null, $longitude=null) {
		if($latitude !== null) {
			$this->latitude = $latitude;
		}
		if($longitude !== null) {
			$this->longitude = $longitude;
		}
	}
	/**
	 * Magic method used to automagically convert
	 * object to string usable in GoogleMap webservices
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function __toString() {
		return $this->latitude.','.$this->longitude;
	}
	private $_address;
	/**
	 * Get address of current location
	 *
	 * @return SwMapAddress
	 * @since  1.10.0
	 */
	public function getAddress() {
		if($this->_address === null) {
			$parameters = array(
				'service' => 'geocode',
				'outputFormat' => 'json',
				'latlng' => $this->__toString(),
				'sensor' => $this->getSensor(),
			);
			// geocode current coordinates
			if($this->getLanguage() !== null) {
				$parameters['language'] = $this->getLanguage();
			}
			$response = $this->map->execute($parameters);
			if(($response->status == 200) && (isset($response->data['results'])) && isset($response->data['results'][0])) {
				//TODO: make sure geocoding always return one address
				$this->_address = Yii::createComponent('ext.sweekit.map.SwMapAddress', $response->data['results'][0]);
			} else {
				throw new CHttpException(503);
			}
		}
		return $this->_address;
	}
	private $_elevation;
	private $_resolution;
	/**
	 * Get elevation of current coordinates
	 *
	 * @return float
	 * @since  1.10.0
	 */
	public function getElevation() {
		if($this->_elevation === null) {
			$parameters = array(
					'service' => 'elevation',
					'outputFormat' => 'json',
					'locations' => $this->__toString(),
					'sensor' => $this->getSensor(),
			);
			$response = $this->map->execute($parameters);
			if(($response->status == 200) && isset($response->data['results']) && isset($response->data['results'][0])) {
				$this->_elevation = $response->data['results'][0]['elevation'];
				$this->_resolution = $response->data['results'][0]['resolution'];
			} else {
				throw new CHttpException(503);
			}
		}
		return $this->_elevation;
	}

}
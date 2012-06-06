<?php
/**
 * SwMapAddress.php
 *
 * PHP version 5.2+
 *
 * Google map address
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
 * This SwMapAddress class handle the logic for
 * google map address
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
 * @property string $country indicates the national political entity, and is typically the highest order type returned by the Geocoder.
 * @property string $formatted
 * @property string $locality indicates an incorporated city or town political entity.
 * @property string $number indicates the precise street number.
 * @property string $route indicates a named route (such as "US 101").
 * @property string $zipCode indicates a postal code as used to address postal mail within the country.
 * @property SwMapGeometry $geometry
 * @property SwMapAddress[] $normalizedAddresses
 *
 */
class SwMapAddress extends SwMapBaseComponent {
	private $_addressComponentTypes = array(
			'street_number' => 'number',
			'route' => 'route',
			'postal_code' => 'zipCode',
			'locality' => 'locality',
			'country' => 'country',
			//TODO: implement other fields if needed
			/*
			 * 'street_address', 'intersection', 'political',
			 * 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3',
			 * 'colloquial_area','sublocality', 'neighborhood', 'premise', 'subpremise',
			 * 'natural_feature', 'airport', 'park', 'point_of_interest', 'post_box',
			 * 'floor', 'room'
			 */
	);

	private $_number = array('short' => null, 'long' => null);
	private $_route = array('short' => null, 'long' => null);
	private $_zipCode = array('short' => null, 'long' => null);
	private $_locality = array('short' => null, 'long' => null);
	private $_country = array('short' => null, 'long' => null);
	private $_formatted;
	private $_geometry;
	private $_accuracy;

	/**
	 * Construct a SwMapAddress object and populate it using
	 * google data if needed
	 *
	 * @param array $data
	 *
	 * @return SwMapAddress
	 */
	public function __construct($data = null) {
		if($data !== null) {
			$this->populateWithGoogleData($data);
		}
	}

	/**
	 * Populate object with some google Data
	 *
	 * @param array $googleData
	 *
	 * @return void
	 * @since  1.10.0
	 */
	protected function populateWithGoogleData($googleData) {
		if(isset($googleData['address_components']) === true) {
			$knownComponents = array_keys($this->_addressComponentTypes);
			foreach($googleData['address_components'] as $component) {
				foreach($component['types'] as $type) {
					if(in_array($type, $knownComponents) === true) {
						$attribute = '_'.$this->_addressComponentTypes[$type];
						$this->$attribute = array(
							'short' => $component['short_name'],
							'long' => $component['long_name'],
						);
					}
				}
			}
		}
		if(isset($googleData['formatted_address']) === true) {
			$this->_formatted = $googleData['formatted_address'];
		}
		if(isset($googleData['geometry']) === true) {
			$this->_geometry = Yii::createComponent('ext.sweekit.map.SwMapGeometry', $googleData['geometry']);
		}
	}

	/**
	 * Get geometry for current address
	 *
	 * @return SwMapGeometry
	 * @since  1.10.0
	 */
	public function getGeometry() {
		return $this->_geometry;
	}

	/**
	 * Try to normalize current address. An array of SwMapAddress is returned with
	 * all found addresses. If the $formatted field is not empty, the search is based
	 * on it. If $formatted field is empty a raw address is built using the fields :
	 * $number, $route, $zipCode, $locality and $country
	 *
	 * @return array
	 * @since  1.10.0
	 */
	public function getNormalizedAddresses() {
		$parameters = array(
				'service' => 'geocode',
				'outputFormat' => 'json',
				'sensor' => 'false',
		);
		if(isset($this->_formatted) === true) {
			$parameters['address'] = $this->_formatted;
		} else {
			$address = null;
			if($this->_number['long'] !== null) {
				$address[] = $this->_number['long'];
			}
			if($this->_route['long'] !== null) {
				$address[] = $this->_route['long'];
			}
			if($this->_zipCode['long'] !== null) {
				$address[] = $this->_zipCode['long'];
			}
			if($this->_locality['long'] !== null) {
				$address[] = $this->_locality['long'];
			}
			if($this->_country['long'] !== null) {
				$address[] = $this->_country['long'];
			}
			$parameters['address'] = implode(', ',$address);
		}
		$response = $this->getMap()->execute($parameters);
		$results = null;
		if(($response->status == 200) && isset($response->data['results'])) {
			$results = array();
			foreach($response->data['results'] as $data) {
				$results[] = Yii::createComponent('ext.sweekit.map.SwMapAddress', $data);
			}
		} else {
			throw new CHttpException(503);
		}
		return $results;
	}

	/**
	 * Define street number
	 *
	 * @param string  $number stree number
	 * @param boolean $short  false to use long description true otherwise
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setNumber($number, $short=false) {
		if($short === true) {
			$this->_number['short'] = $number;
		} else {
			$this->_number['long'] = $number;
		}
	}
	/**
	 * Get current street number
	 *
	 * @param boolean $short  false to use long description true otherwise
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getNumber($short=false) {
		return $short?$this->_number['short']:$this->_number['long'];
	}
	/**
	 * Define street name
	 *
	 * @param string  $route street name
	 * @param boolean $short false to use long description true otherwise
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setRoute($route, $short=false) {
		if($short === true) {
			$this->_route['short'] = $route;
		} else {
			$this->_route['long'] = $route;
		}
	}
	/**
	 * Get current street name
	 *
	 * @param boolean $short false to use long description true otherwise
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getRoute($short=false) {
		return $short?$this->_route['short']:$this->_route['long'];
	}
	/**
	 * Define zipcode
	 *
	 * @param string  $zipCode zip code
	 * @param boolean $short   false to use long description true otherwise
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setZipCode($zipCode, $short=false) {
		if($short === true) {
			$this->_zipCode['short'] = $zipCode;
		} else {
			$this->_zipCode['long'] = $zipCode;
		}
	}
	/**
	 * Get current zipcode
	 *
	 * @param boolean $short false to use long description true otherwise
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getZipCode($short=false) {
		return $short?$this->_zipCode['short']:$this->_zipCode['long'];
	}
	/**
	 * Define locality
	 *
	 * @param string  $zipCode zip code
	 * @param boolean $short   false to use long description true otherwise
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setLocality($locality, $short=false) {
		if($short === true) {
			$this->_locality['short'] = $locality;
		} else {
			$this->_locality['long'] = $locality;
		}
	}
	/**
	 * Get current zipcode
	 *
	 * @param boolean $short false to use long description true otherwise
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getLocality($short=false) {
		return $short?$this->_locality['short']:$this->_locality['long'];
	}
	/**
	 * Define country
	 *
	 * @param string  $country country name
	 * @param boolean $short   false to use long description true otherwise
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setCountry($country, $short=false) {
		if($short === true) {
			$this->_country['short'] = $country;
		} else {
			$this->_country['long'] = $country;
		}
	}
	/**
	 * Get current country
	 *
	 * @param boolean $short false to use long description true otherwise
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getCountry($short=false) {
		return $short?$this->_country['short']:$this->_country['long'];
	}
	/**
	 * Get formatted address (as stated by google)
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getFormatted() {
		return $this->_formatted;
	}
	/**
	 * Define the formatted address. Usefull to
	 * normalize addresses
	 *
	 * @param string $formatted formatted address
	 *
	 * @return void
	 * @since  1.10.0
	 */
	public function setFormatted($formatted) {
		$this->_formatted = $formatted;
	}
}
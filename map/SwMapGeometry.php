<?php
/**
 * SwMapGeometry.php
 *
 * PHP version 5.2+
 *
 * Google map geometry
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
Yii::import('ext.sweekit.map.SwMapCoordinates');

/**
 * This SwMapGeometry class handle the logic for
 * google map geometry stuff. Mainly used in SwMapAddress
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
 * @property SwMapCoordinates $location
 * @property string $locationType @see https://developers.google.com/maps/documentation/geocoding/#Results
 * @property array $viewPort viewPort information array('southwest' => SwMapCoordinates, 'northeast' => SwMapCoordinates) @see https://developers.google.com/maps/documentation/geocoding/#Viewports
 * @property array $bounds bounds information array('southwest' => SwMapCoordinates, 'northeast' => SwMapCoordinates)
 */
class SwMapGeometry extends SwMapBaseComponent {
	private $_location;
	private $_locationType;
	private $_viewPort;
	private $_bounds;
	/**
	 * Construct a SwMapGeometry object and populate it using
	 * google data if needed
	 *
	 * @param array $data
	 *
	 * @return SwMapGeometry
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
		if(isset($googleData['location']) === true) {
			$this->_location = new SwMapCoordinates($googleData['location']['lat'], $googleData['location']['lng']);
		}
		if(isset($googleData['location_type']) === true) {
			$this->_locationType = $googleData['location_type'];
		}
		if(isset($googleData['viewport'])) {
			foreach($googleData['viewport'] as $key => $value) {
				$this->_viewPort[$key] = new SwMapCoordinates($value['lat'], $value['lng']);
			}
		}
		if(isset($googleData['bounds'])) {
			foreach($googleData['bounds'] as $key => $value) {
				$this->_bounds[$key] = new SwMapCoordinates($value['lat'], $value['lng']);
			}
		}
	}
	/**
	 * Get coordinates of current location
	 *
	 * @return SwMapCoordinates
	 * @since  1.10.0
	 */
	public function getLocation() {
		return $this->_location;
	}
	/**
	 * Get information about geocoding request accuracy
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getLocationType() {
		return $this->_locationType;
	}
	/**
	 * Get best viewport information to see the coordinates. Return
	 * an array of coordinates : array('southwest' => SwMapCoordinates, 'notheast' => SwMapCoordinates);
	 *
	 * @return array
	 * @since  1.10.0
	 */
	public function getViewPort() {
		return $this->_viewPort;
	}
	/**
	 * Get best viewport information to see the coordinates. Return
	 * an array of coordinates : array('southwest' => SwMapCoordinates, 'notheast' => SwMapCoordinates);
	 * The bounds are not alwys populated. Values are retrieved from the view port if they do not exists;
	 *
	 * @return array
	 * @since  1.10.0
	 */
	public function getBounds() {
		if($this->_bounds === null) {
			return $this->_viewPort;
		} else {
			return $this->_bounds;
		}
	}
}
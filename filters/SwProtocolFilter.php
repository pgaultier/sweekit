<?php
/**
 * SwProtocolFilter.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  filters
 * @package   Sweeml.filters
 */	

/**
 * This is the filter class which allow forcing http or https.
 * This filter can be applied using this filter config :
 * <code> 
 * ...
 * // all actions will be forced in https except actionParse
 * array(
 * 		'ext.sweekit.filters.SwProtocolFilter - parse',
 * 		'mode' => 'https',
 * ),
 * // parse will be forced in http
 * array(
 * 		'ext.sweekit.filters.SwProtocolFilter + parse',
 * 		'mode' => 'http',
 * ),
 * ...
 * </code>
 *
 * if the mode is not set, nothing will be forced current
 * protocol will be used
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  filters
 * @package   Sweeml.filters
 */
class SwProtocolFilter extends CFilter {
	private $_secure;

	/**
	 * Define http mode we want
	 * 
	 * @param string $mode requested mode : can be http/https
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function setMode($mode) {
		$mode = strtolower($mode);
		switch($mode) {
			case 'https':
				$this->_secure = true;
				break;
			case 'http':
				$this->_secure = false;
				break;
		}
	}
	
	/**
	 * Get wanted http(s) mode
	 * 
	 * @return string
	 * @since  1.1.0
	 */
	public function getIsSecureConnection() {
		return $this->_secure;
	}
	
	/**
	 * Check if we want to force the mode
	 * 
	 * @return boolean
	 * @since  1.1.0
	 */
	public function getIsForcedMode() {
		return CPropertyValue::ensureBoolean($this->_secure !== null);
	}
	
	/**
	 * Performs the pre-action filtering.
	 * @see CFilter::preFilter()
	 * 
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * 
	 * @return boolean
	 * @since  1.1.0
	 */
	protected function preFilter($filterChain) {
		if($this->isForcedMode === true) {
			if(Yii::app()->getRequest()->isSecureConnection !== $this->isSecureConnection) {
				// we have to force the switch
				if($this->isSecureConnection === true) {
					$url = 'https://';
				} else {
					$url = 'http://';
				}
				$url .= Yii::app()->getRequest()->serverName.Yii::app()->getRequest()->requestUri;
				Yii::app()->getRequest()->redirect($url);
				return false;
			}
		}
		return true;
	}
    
	/**
	 * Performs the post-action filtering.
	 *
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 *
	 * @return void
	 * @since  1.1.0
	 */
	protected function postFilter($filterChain) {
		// logic being applied after the action is executed
	}
}
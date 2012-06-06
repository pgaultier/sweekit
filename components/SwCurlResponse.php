<?php
/**
 * SwCurlResponse.php
 *
 * PHP version 5.3+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   Sweeml.components
 */

/**
 * This SwCurlResponse is a simple component
 * used by SwCurlRequest. This is a simple object which
 * encapsulate the response.
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   Sweeml.components
 */
class SwCurlResponse extends CComponent {
	protected $_statusCode;
	protected $_headers;
	protected $_lHeaders;
	protected $_body;
	/**
	 * Create curl response
	 *
	 * @param integer $httpCode http status code
	 * @param array   $headers  response headers
	 * @param string  $body     reponse body
	 *
	 * @return SwCurlResponse
	 */
	public function __construct($statusCode, $headers=null, $body=null) {
		$this->_statusCode = $statusCode;
		$this->_headers = $this->_parseHeaders($headers);
		if(is_array($this->_headers) === true) {
			$this->_lHeaders = array_change_key_case($this->_headers, CASE_LOWER);
		}
		$this->_body = $body;
	}
	/**
	 * Return current status code
	 *
	 * @return integer
	 */
	public function getStatus() {
		return $this->_statusCode;
	}
	/**
	 * Get response headers
	 *
	 * @return array
	 * @since  1.10.0
	 */
	public function getHeaders() {
		return $this->_headers;
	}
	/**
	 * Get response header
	 *
	 * @param string $field header field
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getHeaderField($field) {
		$field = strtolower($field);
		if(isset($this->_lHeaders[$field]) === true) {
			return $this->_lHeaders[$field];
		} else {
			return null;
		}
	}
	/**
	 * Get body as raw data
	 *
	 * @return string
	 * @since  1.10.0
	 */
	public function getRawData() {
		return $this->_body;
	}
	/**
	 * Get body as decoded data
	 *
	 * @return mixed
	 * @since  1.10.0
	 */
	public function getData() {
		if(strncmp('application/json', $this->getHeaderField('Content-Type'), 16) == 0) {
			return json_decode($this->_body, true);
		} else {
			return $this->_body;
		}
	}
	/**
	 * Parse HTTP header string into an assoc array
	 *
	 * @param string $headers
	 *
	 * @return array
	 * @since  1.10.0
	 */
	protected function _parseHeaders($headers) {
		$retVal = array();
		$fields = array_filter(explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headers)));
		foreach ($fields as $field) {
        	if(strncasecmp('http', $field, 4) == 0) {
        		$retVal = array();
        	}else if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if (isset($retVal[$match[1]])) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }
}
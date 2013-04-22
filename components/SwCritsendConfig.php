<?php
/**
 * File SwCritsendConfig.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 */

/**
 * Class SwCritsendConfig
 *
 * This module allow automatic configuration for class SwMailer when SwMailer use
 * critsend.
 *
 * id of the module should be set to "mailer". If not, we will attempt to find
 * correct module.
 *
 * <code>
 * 	'components' => array(
 * 		...
 * 		'mailer' => array(
 * 			'class'=>'ext.sweekit.components.SwCritsendConfig',
 * 			'apiUsername'=>'critsend_username',
 * 			'apiPassword'=>'critsend_password',
 * 			// fields below are predefined as shown
 * 			'encoding'=>'UTF-8',
 * 			'fast' => false
 * 			'hosts' => array(
 * 				'http://mail1.messaging-master.com' => array('default' => true, 'fast' => true),
 * 				'http://mail4.messaging-master.com' => array('default' => false, 'fast' => true),
 * 				'http://mail5.messaging-master.com' => array('default' => false, 'fast' => false),
 * 				'http://mail9.messaging-master.com' => array('default' => false, 'fast' => false),
 * 				'http://mail25.messaging-master.com' => array('default' => false, 'fast' => false),
 * 			),
 * 			'wsdl' => '/api_2.php?wsdl',
 * 		),
 * 		...
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 * @since     XXX
 */
class SwCritsendConfig extends CApplicationComponent {
	/**
	 * @var boolean define status of the module
	 */
	private $_initialized = false;

	/**
	 * @var string define encoding to use
	 */
	private $_encoding = 'UTF-8';

	/**
	 * Encoding setter
	 *
	 * @param string $encoding encoding used to send emails
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setEncoding($encoding) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwCritsendConfig, encoding can be defined only in configuration'));
		}
		$this->_encoding = $encoding;
	}

	/**
	 * Encoding getter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getEncoding() {
		return $this->_encoding;
	}

	/**
	 * @var string define replyto email
	 */
	private $_replyTo;

	/**
	 * Reply setter
	 *
	 * @param string $replyTo email to reply to
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setReplyTo($replyTo) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwCritsendConfig, replyTo can be defined only in configuration'));
		}
		$this->_replyTo = $replyTo;
	}

	/**
	 * Replyto getter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getReplyTo() {
		return $this->_replyTo;
	}

	/**
	 * @var array define from email
	 */
	private $_from;

	/**
	 * Define the from field
	 *
	 * @param string $email email for reply to
	 * @param string $name  readable name
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setFrom($email, $name=null) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwCritsendConfig, from can be defined only in configuration'));
		}
		$this->_from = array(
			'mailfrom' => $email,
		);
		if($name !== null) {
			$this->_from['mailfrom_friendly'] = $name;

		}
	}

	/**
	 * From getter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getFrom() {
		return $this->_from;
	}

	/**
	 * @var string define api username to use
	 */
	private $_apiUsername = null;

	/**
	 * Api Username setter
	 *
	 * @param string $apiUsername apie username used to send emails
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setApiUsername($apiUsername) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwCritsendConfig, api username can be defined only in configuration'));
		}
		$this->_apiUsername = $apiUsername;
	}

	/**
	 * Api Username getter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getApiUsername() {
		return $this->_apiUsername;
	}

	/**
	 * @var string define password to use
	 */
	private $_apiPassword = null;

	/**
	 * Api Username setter
	 *
	 * @param string $apiPassword api password used to send emails
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setApiPassword($apiPassword) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwCritsendConfig, api password can be defined only in configuration'));
		}
		$this->_apiPassword = $apiPassword;
	}

	/**
	 * Api Username getter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getApiPassword() {
		return $this->_apiPassword;
	}

	/**
	 * @var string define wsdl resource
	 */
	private $_wsdl = '/api_2.php?wsdl';

	/**
	 * Wsdl setter
	 *
	 * @param string $wsdl wsdl resource name
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setWsdl($wsdl) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwCritsendConfig, wsdl can be defined only in configuration'));
		}
		$this->_wsdl = $wsdl;
	}

	/**
	 * Wsdl getter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getWsdl() {
		return $this->_wsdl;
	}

	/**
	 * @var array define hosts with parameters
	 */
	private $_hosts = array(
						'http://mail1.messaging-master.com' => array('default' => true, 'fast' => true),
						'http://mail4.messaging-master.com' => array('default' => false, 'fast' => true),
						'http://mail5.messaging-master.com' => array('default' => false, 'fast' => false),
 						'http://mail9.messaging-master.com' => array('default' => false, 'fast' => false),
						'http://mail25.messaging-master.com' => array('default' => false, 'fast' => false),
					);

	/**
	 * Hosts setter
	 *
	 * @param array $hosts define hos in array form : array('hostname' => array('default' => bool, 'fast' => bool))
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setHosts($hosts) {
		if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwCritsendConfig, hosts can be defined only in configuration'));
		}
		$this->_hosts = $hosts;
	}

	/**
	 * @var boolean check if hosts have been shuffled
	 */
	private $_shuffled = false;
	/**
	 * Hosts getter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getHosts() {
		if($this->_shuffled === false) {
			$this->_shuffled = true;
			$keys = array_keys($this->_hosts);
			shuffle($keys);
			$shuffledHosts = array();
			foreach($keys as $key) {
				$shuffledHosts[$key] = $this->_hosts[$key];
			}
			$this->_hosts = $shuffledHosts;
		}
		return $this->_hosts;
	}

	/**
	 * Init module with parameters @see CApplicationComponent::init()
	 *
	 * @return void
	 * @since  XXX
	 */
	public function init() {
    	$this->attachBehaviors($this->behaviors);
		$this->_initialized = true;
	}
}
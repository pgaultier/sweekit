<?php
/**
 * File SwMailerCritsend.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 */

Yii::import('ext.sweekit.web.SwMailer');

/**
 * Class SwMailerCritsend wraps @see critsend mailer into
 * an Yii object
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
class SwMailerCritsend extends SwMailer {

	/**
	 * @var boolean security check to avoid singleton misuse
	 */
	private static $_selfCheck = false;
	/**
	 * @var SwMailerCritsend singleton instance
	 */
	private static $_mailerInstance;

	/**
	 * @var SwCritsendConfig app config
	 */
	private $_appConfig;

	/**
	 * Constructor, build current object
	 *
	 * @return SwMailerCritsend
	 * @since  XXX
	 */
	public function __construct() {
		try {
			Yii::trace('Trace: '.__CLASS__.'::'.__FUNCTION__.'()', 'ext.sweekit.web');
			$module = Yii::app()->getComponent('mailer');
			if($module===null) {
				Yii::log(Yii::t('sweelix', '{object} has not been defined', array('{object}'=>'SwCritsendConfig')), CLogger::LEVEL_ERROR, 'ext.sweekit.web');
				throw new CException(Yii::t('sweelix', 'SwCritsendConfig, component has not been defined'));
			}
			$this->_appConfig = $module;
		} catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'ext.sweekit.web');
			throw $e;
		}
	}

	/**
	 * Send email to multiple users
	 *
	 * @param mixed  $campaign name used to filter emails, can be a string or an array of strings
	 * @param array  $users    users must be an array of array : array(array('email' => 'user@email.com', 'name' => 'User name'), ...)
	 *
	 * @return boolean;
	 * @since  XXX
	 */
	public function sendCampaign($campaign, $users) {
		$result = false;
		if($this->_preparedContent !== null) {
			if(is_array($campaign) === false) {
				$campaign = array($campaign);
			}
			for($i=0; $i< count($users); $i++) {
				for($j = 1; $j < 16; $j++) {
					$fieldName = 'field'.$j;
					if (array_key_exists($fieldName, $users[$i]) === false){
						$users[$i][$fieldName] = '';
					}
				}
			}

			$parameters = array_merge(array('tag' => $campaign), $this->getFrom(), $this->getReplyTo());
			$result = $this->getSoapClient()->sendCampaign($this->generateAuthenticationToken(), $users, $parameters, $this->_preparedContent);
		}
		return $result;
	}

	/**
	 * Send an email to one user
	 *
	 * @param string $campaign name used to filter emails
	 * @param string $email    target user email
	 * @param string $name     target user name
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function send($campaign, $email, $name=null) {
		return $this->sendCampaign($campaign, array(array('email' => $email)));
	}

	/**
	 * @var array content to send
	 */
	private $_preparedContent;
	/**
	 * Define content to send
	 *
	 * @param string $subject     email subject
	 * @param string $htmlBody    html used to populate the email
	 * @param string $textualBody text used for the email
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setContent($subject, $htmlBody=null, $textualBody=null) {
		$this->_preparedContent = array(
			'subject' => $subject,
			'text' => $textualBody,
			'html' => $htmlBody,
		);
	}

	/**
	 * @var array replyTo parameters
	 */
	private $_replyTo;

	/**
	 * Define the replyTo field
	 *
	 * @param string $email email for reply to
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setReplyTo($email) {
		$this->_replyTo = array(
			'replyto' => $email,
			'replyto_filtered' => true,
		);
	}

	/**
	 * Retrieve current replyTo settings array('email' => $email, 'name' => $name)
	 *
	 * @return array
	 * @since  XXX
	 */
	public function getReplyTo() {
		if($this->_replyTo === null) {
			$this->_replyTo = array(
				'replyto' => $this->_appConfig->getReplyTo(),
				'replyto_filtered' => true
			);
		}
		return $this->_replyTo;
	}

	/**
	 * @var array from parameters
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
		$this->_from = array('mailfrom' => $email);
		if($name !== null) {
			$this->_from['mailfrom_friendly'] = $name;
		}
	}

	/**
	 * Retrieve current from settings array('email' => $email, 'name' => $name)
	 *
	 * @return array
	 * @since  XXX
	 */
	public function getFrom() {
		if($this->_from === null) {
			$this->_from = $this->_appConfig->getFrom();
		}
		return $this->_from;
	}

	/**
	 * Create a new tag
	 *
	 * @param string $tag
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function createTag($tag) {
		return $this->getSoapClient()->createTag($this->generateAuthenticationToken(), $tag);
	}

	/**
	 * Delete a tag
	 *
	 * @param string $tag
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function deleteTag($tag) {
		return $this->getSoapClient()->deleteTag($this->generateAuthenticationToken(), $tag);
	}

	/**
	 * Check tag existence
	 *
	 * @param string $tag
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function isTag($tag) {
		return $this->getSoapClient()->isTag($this->generateAuthenticationToken(), $tag);
	}

	/**
	 * Generate authentication token, needed to perform call
	 *
	 * @return array
	 * @since  XXX
	 */
	protected function generateAuthenticationToken() {
		//TODO Memory leak here of 32 octets: use date instead as in comment below
		// $timestamp = gmstrftime("%Y-%m-%dT%H:%M:%SZ", time());
		$timestamp = date('c');
		return array(
			'user' => $this->_appConfig->getApiUsername(),
			'timestamp'=> $timestamp,
			'signature' => hash_hmac("sha256", "http://mxmaster.net/campaign/0.1#doCampaign".$this->_appConfig->getApiUsername().$timestamp, $this->_appConfig->getApiPassword())
		);
	}

	/**
	 * Create / Return the singleton for current mailing
	 * system
	 *
	 * @return SwMailerCritsend
	 * @since  XXX
	 */
	public static function getInstance() {
		try {
			Yii::trace('Trace: '.__CLASS__.'::'.__FUNCTION__.'()', 'ext.sweekit.web');
			if(self::$_mailerInstance === null) {
				self::$_selfCheck = true;
				self::$_mailerInstance = new SwMailerCritsend();
				self::$_selfCheck = false;
			}
			return self::$_mailerInstance;
		} catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'ext.sweekit.web');
			throw $e;
		}
	}

	/**
	 * @var SoapClient soapclient instance
	 */
	private $_soapClient;

	/**
	 * Prepare soapclient
	 *
	 * @return SoapClient
	 * @since  XXX
	 */
	protected function getSoapClient() {
		try {
			Yii::trace('Trace: '.__CLASS__.'::'.__FUNCTION__.'()', 'ext.sweekit.web');
			if($this->_soapClient === null) {
				foreach($this->_appConfig->getHosts() as $host => $config) {
					try {
						$host = $host.$this->_appConfig->getWsdl();
						$this->_soapClient = new SoapClient($host, array(
								'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
								'trace' => true,
								'encoding' => $this->_appConfig->getEncoding(),
						));
					} catch (SoapFault $e) {
						$this->_soapClient = false;
					}
					if($this->_soapClient !== false) {
						break;
					}
				}
			}
			return $this->_soapClient;
		} catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'ext.sweekit.web');
			throw $e;
		}
	}
}
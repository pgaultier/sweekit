<?php
/**
 * File SwCritsendMailer.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  mailer
 * @package   sweekit.mailer
 */

Yii::import('ext.sweekit.mailer.SwMailer');

/**
 * Class SwCritsendMailer wraps @see critsend mailer into
 * an Yii object
 *
 * <code>
 * 		'connector' => array(
 * 			'class' => 'ext.sweekit.mailer.SwCritsendMailer',
 * 			'apiUsername' => 'username',
 * 			'apiPassword' => 'apikey',
 * 		),
 * </code>
 *
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  mailer
 * @package   sweekit.mailer
 * @since     2.0.0
 */
class SwCritsendMailer extends SwMailer {

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
	 * @since  2.0.0
	 */
	public function setEncoding($encoding) {
		$this->_encoding = $encoding;
	}

	/**
	 * Encoding getter
	 *
	 * @return string
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	public function setReplyTo($replyTo) {
		$this->_replyTo = array(
				'replyto' => $replyTo,
				'replyto_filtered' => true,
		);
	}

	/**
	 * Replyto getter
	 *
	 * @return string
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	public function setFrom($email, $name=null) {
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
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	public function setApiUsername($apiUsername) {
		$this->_apiUsername = $apiUsername;
	}

	/**
	 * Api Username getter
	 *
	 * @return string
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	public function setApiPassword($apiPassword) {
		$this->_apiPassword = $apiPassword;
	}

	/**
	 * Api Username getter
	 *
	 * @return string
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	public function setWsdl($wsdl) {
		$this->_wsdl = $wsdl;
	}

	/**
	 * Wsdl getter
	 *
	 * @return string
	 * @since  2.0.0
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
	 * @since  2.0.0
	*/
	public function setHosts($hosts) {
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
	 * @since  2.0.0
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
	 * Send email to multiple users
	 *
	 * @param mixed  $campaign name used to filter emails, can be a string or an array of strings
	 * @param array  $users    users must be an array of array : array(array('email' => 'user@email.com', 'name' => 'User name'), ...)
	 *
	 * @return boolean;
	 * @since  2.0.0
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
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	public function setContent($subject, $htmlBody=null, $textualBody=null) {
		$this->_preparedContent = array(
			'subject' => $subject,
			'text' => $textualBody,
			'html' => $htmlBody,
		);
	}

	/**
	 * Create a new tag
	 *
	 * @param string $tag
	 *
	 * @return boolean
	 * @since  2.0.0
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
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	public function isTag($tag) {
		return $this->getSoapClient()->isTag($this->generateAuthenticationToken(), $tag);
	}

	/**
	 * Generate authentication token, needed to perform call
	 *
	 * @return array
	 * @since  2.0.0
	 */
	protected function generateAuthenticationToken() {
		$timestamp = date('c');
		return array(
			'user' => $this->getApiUsername(),
			'timestamp'=> $timestamp,
			'signature' => hash_hmac("sha256", "http://mxmaster.net/campaign/0.1#doCampaign".$this->getApiUsername().$timestamp, $this->getApiPassword())
		);
	}

	/**
	 * @var SoapClient soapclient instance
	 */
	private $_soapClient;

	/**
	 * Prepare soapclient
	 *
	 * @return SoapClient
	 * @since  2.0.0
	 */
	protected function getSoapClient() {
		try {
			Yii::trace('Trace: '.__CLASS__.'::'.__FUNCTION__.'()', 'ext.sweekit.mailer');
			if($this->_soapClient === null) {
				foreach($this->getHosts() as $host => $config) {
					try {
						$host = $host.$this->getWsdl();
						$this->_soapClient = new SoapClient($host, array(
								'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
								'trace' => true,
								'encoding' => $this->getEncoding(),
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
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'ext.sweekit.mailer');
			throw $e;
		}
	}
}
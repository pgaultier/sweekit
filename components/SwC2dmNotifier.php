<?php
/**
 * SwC2dmNotifier.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.11.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweekit.components
 */

/**
 * This SwC2dmNotifier allow users to send
 * notification to Android devices througs C2DM platform.
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.11.0
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweekit.components
 *
 * @property string $certificatePath
 * @property string $temporaryCertificatePath
 */
class SwC2dmNotifier extends CComponent implements SwMobileNotifierInterface {
	/**
	 * Google constants
	 */
	const C2DM_LOGIN_URL = 'https://www.google.com/accounts/ClientLogin';
	const C2DM_PUSH_URL = 'https://android.apis.google.com/c2dm/send';
	const C2DM_SERVICE = 'ac2dm';
	const C2DM_ACCOUNT_TYPE = 'HOSTED_OR_GOOGLE';
	const DIRECTORY_CREATE_MASK = 0777;
	const C2DM_RUNTIME_PATH = 'application.runtime.android';
	const C2DM_CERTIFICATES_PATH = 'application.config.certificates';

	public $username;
	public $password;
	public $applicationIdentifier;

	private $_certificatePath;

	/**
	 * Define the certificates path in Yii pathalias form
	 *
	 * @param string $certificatePath
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function setCertificatePath($certificatePath) {
		$this->_certificatePath = Yii::getPathOfAlias($certificatePath).DIRECTORY_SEPARATOR;
	}

	/**
	 * Get current certificate path. This method
	 * returns a real path
	 *
	 * @return string
	 * @since  1.11.0
	 */
	public function getCertificatePath() {
		if($this->_certificatePath === null) {
			$this->_certificatePath = Yii::getPathOfAlias(self::C2DM_CERTIFICATES_PATH).DIRECTORY_SEPARATOR;
		}
		return $this->_certificatePath;
	}

	/**
	 * @var string where to store the bundle and the embedded ca files
	 */
	private $_temporaryCertificateFile;

	/**
	 * Define the certificates path in Yii pathalias form
	 *
	 * @param string $certificatePath
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function setTemporaryCertificatePath($certificatePath) {
		$this->_temporaryCertificateFile = Yii::getPathOfAlias($certificatePath).DIRECTORY_SEPARATOR;
	}

	/**
	 * Get current certificate path. This method
	 * returns a real path
	 *
	 * @return string
	 * @since  1.11.0
	 */
	public function getTemporaryCertificatePath() {
		if($this->_temporaryCertificateFile === null) {
			$this->_temporaryCertificateFile = Yii::getPathOfAlias(self::C2DM_RUNTIME_PATH).DIRECTORY_SEPARATOR;
		}
		return $this->_temporaryCertificateFile;
	}

	/**
	 * @var boolean define if we are in production or not
	 */
	public $isProduction=true;

	/**
	 * @var string name of the certificate file in DER format (direct from apple)
	 */
	public $caFile;

	/**
	 * @var boolean set to true if we have to embed the cafile (usefull when ssl dir is outdated)
	 */
	public $embeddedCaFile=false;

	private $_currentMessageId=0;
	private $_messageQueue = array();

	/**
	 * Prepare one or more messages
	 * @see SwMobileNotifierInterface::prepare()
	 *
	 *
	 * @param mixed $deviceIds  string if one device is the target else an array with the list of all targets
	 * @param array $payload    an array which contains all the data to send.
	 * @param array $parameters an array of extended parameters (collapse_key, delay_while_idle).
	 */
	public function prepare($deviceIds, $payload, $parameters=array()) {
		if(is_array($deviceIds) === false) {
			$deviceIds = array($deviceIds);
		}
		$data = $parameters;
		foreach($payload as $key => $value) {
			$data['data.'.$key] = $value;
		}

		foreach($deviceIds as $deviceId) {
			$messageId = (++$this->_currentMessageId);
			$this->_messageQueue[$messageId] = $data;
			$this->_messageQueue[$messageId]['registration_id'] = $deviceId;
			if(isset($data['collapse_key']) === false) {
				$this->_messageQueue[$messageId]['collapse_key'] = md5(implode(";",$payload));
			}
		}
	}
	private $_sentQueue=array();

	/**
	 * Send the notifications
	 *
	 * @return void
	 * @since  1.11.0
	 */
	public function notify() {
		foreach($this->_messageQueue as $messageId => $payload) {

			$curl = curl_init(self::C2DM_PUSH_URL);
			if($this->embeddedCaFile === true) {
				curl_setopt($curl, CURLOPT_CAINFO, $this->findCaFile());
				//TODO: remove Verifier to false when google sets a valid certificate
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				//TODO: check if sslv3 is supported curl_setopt($curl, CURLOPT_SSLVERSION, 3);
			}
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: GoogleLogin auth='. $this->getAuthenticationToken()));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
			$response = curl_exec($curl);
			curl_getinfo($curl);
			curl_close($curl);
			if(stripos($response, 'Error') === false) {
				$this->_sentQueue[$messageId] = array(
						'success' => true,
						'code' => 0,
						'response' => $response,
						'messageId' => $messageId
				);
			} else {
				$this->_sentQueue[$messageId] = array(
						'success' => false,
						'code' => 999,
						'response' => $response,
						'messageId' => $messageId
				);
			}
		}
	}

	private $_currentToken;
	/**
	 * Retrieve the current authentication token
	 *
	 * @param boolean $forceRefresh force token refresh
	 *
	 * @return string
	 */
	protected function getAuthenticationToken($forceRefresh=false) {
		Yii::trace('Trace: '.__CLASS__.'::'.__FUNCTION__.'()', 'sweekit.components');
		try {
			if(($this->_currentToken === null) || ($forceRefresh === true)) {
				$curl = curl_init(self::C2DM_LOGIN_URL);
				if($this->embeddedCaFile === true) {
					curl_setopt($curl, CURLOPT_CAINFO, $this->findCaFile());
					//TODO: remove Verifier to false when google sets a valid certificate
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					//TODO: check if sslv3 is supported curl_setopt($curl, CURLOPT_SSLVERSION, 3);
				}
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, array(
					'accountType' => self::C2DM_ACCOUNT_TYPE,
					'Email' => $this->username,
					'Passwd' => $this->password,
					'service' => self::C2DM_SERVICE,
					'source' => $this->applicationIdentifier,
				));
				$response = curl_exec($curl);
				$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				$response = preg_split('/[\r\n]+/', $response);
				$token = null;
				curl_close($curl);
				if($statusCode == 200) {
					foreach($response as $responseLine) {
						if(($pos = strpos($responseLine, 'Auth=')) !== false) {
							$token = substr($responseLine, 5);
							break;
						}
					}
				} elseif($statusCode == 403) {
					foreach($response as $responseLine) {
						if(($pos = strpos($responseLine, 'Error=')) !== false) {
							$token = substr($responseLine, 6);
							break;
						}
					}
					throw new CException('Google authentication failed with error : '.$token);
				} else {
					throw new CException('Google authentication unknown error');
				}
				$this->_currentToken = $token;
			}
			return $this->_currentToken;
		}
		catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'sweekit.components');
			throw $e;
		}
	}

	/**
	 * Get status for current queue.
	 *
	 * @see SwMobileNotifierInterface::getStatus()
	 *
	 * @return array
	 * @since  1.11.0
	 */
	public function getStatus() {
		return $this->_sentQueue;
	}


	private $_caFile;

	/**
	 * Find correct real path for CA file. only used when embbed CA file is active
	 *
	 * @return string
	 * @since  1.11.0
	 */
	protected function findCaFile() {
		if($this->_caFile === null) {
			if(($this->caFile !== null) && (is_file($this->getCertificatePath().$this->caFile) === true)) {
				$this->_caFile = $this->getCertificatePath().$this->caFile;
			} else {
				$this->_caFile = $this->embbedCaFile();
			}
		}
		return $this->_caFile;
	}

	/**
	 * Prepare ca certificate. Usefull when certificate are not
	 * fully installed on environment
	 *
	 * @return void
	 * @since  1.11.0
	 */
	protected function embbedCaFile() {
		$tempCaFile = $this->getTemporaryCertificatePath().'c2dm_thawte_ca.pem';
		if(is_file($tempCaFile) === false) {
			if(is_dir($this->getTemporaryCertificatePath()) === false) {
				mkdir($this->getTemporaryCertificatePath(), self::DIRECTORY_CREATE_MASK, true);
			}
			$cert = <<<EOC
-----BEGIN CERTIFICATE-----
MIICPDCCAaUCEDyRMcsf9tAbDpq40ES/Er4wDQYJKoZIhvcNAQEFBQAwXzELMAkG
A1UEBhMCVVMxFzAVBgNVBAoTDlZlcmlTaWduLCBJbmMuMTcwNQYDVQQLEy5DbGFz
cyAzIFB1YmxpYyBQcmltYXJ5IENlcnRpZmljYXRpb24gQXV0aG9yaXR5MB4XDTk2
MDEyOTAwMDAwMFoXDTI4MDgwMjIzNTk1OVowXzELMAkGA1UEBhMCVVMxFzAVBgNV
BAoTDlZlcmlTaWduLCBJbmMuMTcwNQYDVQQLEy5DbGFzcyAzIFB1YmxpYyBQcmlt
YXJ5IENlcnRpZmljYXRpb24gQXV0aG9yaXR5MIGfMA0GCSqGSIb3DQEBAQUAA4GN
ADCBiQKBgQDJXFme8huKARS0EN8EQNvjV69qRUCPhAwL0TPZ2RHP7gJYHyX3KqhE
BarsAx94f56TuZoAqiN91qyFomNFx3InzPRMxnVx0jnvT0Lwdd8KkMaOIG+YD/is
I19wKTakyYbnsZogy1Olhec9vn2a/iRFM9x2Fe0PonFkTGUugWhFpwIDAQABMA0G
CSqGSIb3DQEBBQUAA4GBABByUqkFFBkyCEHwxWsKzH4PIRnN5GfcX6kb5sroc50i
2JhucwNhkcV8sEVAbkSdjbCxlnRhLQ2pRdKkkirWmnWXbj9T/UWZYB2oK0z5XqcJ
2HUw19JlYD1n1khVdWk/kfVIC0dpImmClr7JyDiGSnoscxlIaU5rfGW/D/xwzoiQ
-----END CERTIFICATE-----
EOC;
			// we should create it
			file_put_contents($tempCaFile, $cert);
		}
		return $tempCaFile;
	}
}

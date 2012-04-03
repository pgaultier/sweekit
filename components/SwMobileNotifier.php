<?php
/**
 * SwMobileNotififier.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  components
 * @package   Sweeml.components
 */

/**
 * This SwMobileNotififier is an application component
 * which allow users to send notification to mobile devices.
 * Currently supported systems are
 *  * apns (iOS)
 *  * c2dm (Android)
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  components
 * @package   Sweeml.components
 */	
class SwMobileNotifier extends CApplicationComponent {
	/**
	 * Apple constants
	 */
	const APNS_PUSH_URL = 'ssl://gateway.push.apple.com:2195';
	const APNS_SANDBOX_PUSH_URL = 'ssl://gateway.sandbox.push.apple.com:2195';
	const APNS_FEEDBACK_URL = 'ssl://feedback.push.apple.com:2196';
	const APNS_SANDBOX_FEEDBACK_URL = 'ssl://feedback.sandbox.push.apple.com:2196';
	/**
	 * Google constants
	 */
	const C2DM_LOGIN_URL = 'https://www.google.com/accounts/ClientLogin';
	const C2DM_PUSH_URL = 'https://android.apis.google.com/c2dm/send';
	const C2DM_SERVICE = 'ac2dm';
	const C2DM_ACCOUNT_TYPE = 'HOSTED_OR_GOOGLE';
	
	/**
	 * @var string mode : production or devel
	 */
	public $mode;
	
	/**
	 * @var string path to certificate file in PEM format
	 */
	public $apnsCertificateFile;
	/**
	 * @var string passphrase for current certificate
	 */
	public $apnsCertificatePassphrase;
	/**
	 * @var boolean set to true if we have to embed the cafile (usefull when ssl dir is outdated)
	 */
	public $apnsEmbeddedCaFile=false;
	/**
	 * @var boolean check if apns is available
	 */
	private $_apnsEnabled = false;
	/**
	 * @var string certificate file
	 */
	private $_apnsCertificateFile;
	/**
	 * @var string certificate authority file
	 */
	private $_apnsCaFile;
	/**
	 * @var string google account username
	 */
	public $c2dmUsername;
	/**
	 * @var string google account password
	 */
	public $c2dmPassword;
	/**
	 * @var string google application identifier
	 */
	public $c2dmApplicationIdentifier;
	/**
	 * @var boolean check if c2dm is available
	 */
	/**
	 * @var boolean set to true if we have to embed the cafile (usefull when ssl dir is outdated)
	 */
	public $c2dmEmbeddedCaFile=false;
	/**
	 * @var string certificate authority file
	 */
	private $_c2dmCaFile;
	private $_c2dmEnabled = false;
	
	
	/**
	 * Initializes the application component.
	 * This method is required by {@link IApplicationComponent} and is invoked by application.
	 * 
	 * @return void
	 * @since  XXX
	 */
	public function init() {
		if(($this->mode === null) || (in_array($this->mode, array('devel', 'production')) === false)) {
			throw new CException('SwMobileNotififier mode must be defined to \'devel\' or \'production\'');
		}
		if($this->apnsCertificateFile !== null) {
			$this->_apnsCertificateFile = Yii::getPathOfAlias('application.config').DIRECTORY_SEPARATOR.$this->apnsCertificateFile;
			if(file_exists($this->_apnsCertificateFile) === true) {
				$this->_apnsEnabled = true;
			}
		}
		if($this->_apnsEnabled === true) {
			// prepare ca certificates
			$this->_initApnsCa();
		}
		if(($this->c2dmUsername !== null) && ($this->c2dmPassword !== null) && ($this->c2dmApplicationIdentifier !== null)) {
			$this->_c2dmEnabled = true;
		}
		if($this->_c2dmEnabled === true) {
			// prepare ca certificates
			$this->_initC2dmCa();
		}
		parent::init();
	}
	private $_apnsStream;
	private $_apnsError;
	private $_apnsErrorString;
	/**
	 * Send a notification message to an ios device
	 * returned data is like array( array($statusBoolean, $pushId, $serviceResponse), ...)
	 * iOS message are formatted like this : 
	 * array('aps' => array(
	 * 	'badge' => (integer)5, // value to display as a badge
	 * 	'alert' => (string)'Message to display to the end user', // display the alert to the user
	 * 	// @see http://developer.apple.com/library/mac/#documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/ApplePushService/ApplePushService.html
	 * ))
	 * 
	 * @param mixed  $pushId  pushId of target, array for multiple targets
	 * @param array  $message message check apple documentation to prepare correct array
	 * 
	 * @return mixed
	 * @since  XXX
	 */
	public function sendApnsMessage($pushId, $message) {
		$result = false;
		if($this->_apnsEnabled === true) {
			$result = array();
			$this->_apnsOpenStream();
			$payload = json_encode($message);
			if(!is_array($pushId)) {
				$pushId = array($pushId);
			}
			foreach($pushId as $realPushId) {
				$res = $this->_apnsWriteStream($realPushId, $payload);
				if($res === false) {
					$result[] = array(false, $realPushId, Yii::t('sweelix', 'Error while writing message'));
				} else {
					$result[] = array(true, $realPushId, Yii::t('sweelix', 'Push sent'));
				}
			}
			$this->_apnsCloseStream();
		}
		return $result;
	}

	/**
	 * Reads info from feedback server in order
	 * to revoke app registrations
	 * returned data is like array( array($timestamp, $pushId), ...)
	 * 
	 * @return mixed
	 * @since  XXX
	 */
	public function readApnsFeedback() {
		$result = false;
		if($this->_apnsEnabled === true) {
			$this->_apnsOpenStream(true);
			$result = $this->_apnsReadStream();
			$this->_apnsCloseStream();
		}
		return $result;
	}

	/**
	 * Open stream to write messages
	 * 
	 * @param boolean $feedback do we open a socket for feedback ?
	 * 
	 * @return void
	 * @since  XXX
	 */
	private function _apnsOpenStream($feedback=false) {
		Yii::trace('Trace: '.__CLASS__.'::'.__FUNCTION__.'()', 'Sweeml.components');
		try {
			$streamContext = stream_context_create();
			stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->_apnsCertificateFile);
			if($this->apnsCertificatePassphrase !== null) {
				stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->apnsCertificatePassphrase);
			}
			if($this->apnsEmbeddedCaFile === true) {
				stream_context_set_option($streamContext, 'ssl', 'cafile', $this->_apnsCaFile);
			}
			if($feedback === false) {
				if($this->mode === 'production') {
					$this->_apnsStream = stream_socket_client(self::APNS_PUSH_URL, $this->_apnsError, $this->_apnsErrorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
				} else {
					$this->_apnsStream = stream_socket_client(self::APNS_SANDBOX_PUSH_URL, $this->_apnsError, $this->_apnsErrorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
				}
			} else {
				if($this->mode === 'production') {
					$this->_apnsStream = stream_socket_client(self::APNS_FEEDBACK_URL, $this->_apnsError, $this->_apnsErrorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
				} else {
					$this->_apnsStream = stream_socket_client(self::APNS_SANDBOX_FEEDBACK_URL, $this->_apnsError, $this->_apnsErrorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
				}
			}
			if($this->_apnsStream === false) {
				throw new CException('Unable to open socket');
			}
		}
		catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'Sweeml.components');
			throw $e;
		}
	}
	
	/**
	 * Write message to current stream and return number
	 * of bytes written or false on error
	 * 
	 * @param string $pushId  target token Id
	 * @param string $payload json message to send
	 * 
	 * @return mixed
	 * @since  XXX
	 */
	private function _apnsWriteStream($pushId, $payload) {
		$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $pushId)) . chr(0) . chr(strlen($payload)) . $payload;
		return fwrite($this->_apnsStream, $apnsMessage);
	}
	
	/**
	 * Read message from current stream and return list
	 * of pushId which should be revoked
	 * 
	 * @return array
	 * @since  XXX
	 */
	private function _apnsReadStream() {
		$result = array();
		while(feof($this->_apnsStream) !== true) {
			$data = fread($this->_apnsStream, 38);
			if(strlen($data)>0) {
				$data = unpack("N1timestamp/n1length/H*pushId", $data);
				$result[] = array($data['timestamp'], $data['pushId']);
			}
		}
		return $result;
	}
	
	/**
	 * Close stream when message where writtend
	 * 
	 * @return void
	 * @since  XXX
	 */
	private function _apnsCloseStream() {
		fclose($this->_apnsStream);
	}
	
	/**
	 * Send push message to c2dm platform
	 * return an array for arrays if c2dm is enabled false otherwise.
	 * returned data is like array( array($statusBoolean, $pushId, $serviceResponse), ...)
	 * 
	 * @param mixed   $pushId         pushId of target, array for multiple targets
	 * @param array   $message        message check android documentation to prepare correct array
	 * @param boolean $delayWhileIdle delayWhileIdle If set as true, this will wait until the device wakes up to send the push notification.
	 * @param string  $collapseKey    collapseKey An arbitrary string that is used to collapse a group of like messages when the device is offline, so that only the last message gets sent to the client.
	 * 
	 * @return mixed
	 * @since  XXX
	 */
	public function sendC2dmMessage($pushId, $message, $delayWhileIdle=false, $collapseKey=null) {
		$result = false;
		if($this->_c2dmEnabled === true) {
			$result = array();
			$post = array(
				'collapse_key' => ($collapseKey ? $collapseKey : md5(implode(";",$message))),
			);
			foreach($message as $k => $v) $post['data.'.$k] = $v;
			if($delayWhileIdle) $post['delay_while_idle'] = 'true';
			if(!is_array($pushId)) {
				$pushId = array($pushId);
			}
			foreach($pushId as $realPushId) {
				$post['registration_id'] = $realPushId;
				$curl = curl_init(self::C2DM_PUSH_URL);
				if($this->c2dmEmbeddedCaFile === true) {
					curl_setopt($curl, CURLOPT_CAINFO, $this->_c2dmCaFile);
					//TODO: remove Verifier to false when google sets a valid certificate
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
					//TODO: check if sslv3 is supported curl_setopt($curl, CURLOPT_SSLVERSION, 3);
				}
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_HEADER, true);
				curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: GoogleLogin auth='. $this->_c2dmGetAuthenticationToken()));
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
				$response = curl_exec($curl);
				var_dump(curl_error($curl), $response);exit;
				curl_getinfo($curl);
				curl_close($curl);
				if(stripos($response, 'Error') === false) { 
					$result[] = array(false, $realPushId, $response);
				} else {
					$result[] = array(true, $realPushId, $response);
				}
			}
		}
		return $result;
	}
	
	private $_currentToken;
	/**
	 * Retrieve the current authentication token
	 * 
	 * @param boolean $forceRefresh force token refresh
	 * 
	 * @return string
	 */
	private function _c2dmGetAuthenticationToken($forceRefresh=false) {
		Yii::trace('Trace: '.__CLASS__.'::'.__FUNCTION__.'()', 'Sweeml.components');
		try {
			if(($this->_currentToken === null) || ($forceRefresh === true)) {
				$curl = curl_init(self::C2DM_LOGIN_URL);
				if($this->c2dmEmbeddedCaFile === true) {
					curl_setopt($curl, CURLOPT_CAINFO, $this->_c2dmCaFile);
				}
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, array(
						'accountType' => self::C2DM_ACCOUNT_TYPE,
						'Email' => $this->c2dmUsername,
						'Passwd' => $this->c2dmPassword,
						'service' => self::C2DM_SERVICE,
						'source' => $this->c2dmApplicationIdentifier,
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
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'Sweeml.components');
			throw $e;
		}
	}
	
	private function _initC2dmCa() {
		if($this->c2dmEmbeddedCaFile === true) {
			$googleDir = Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'google';
			if(is_dir($googleDir) === false) {
				mkdir($googleDir);
			}
			$this->_c2dmCaFile = $googleDir.DIRECTORY_SEPARATOR.'c2dm_thawte_ca.pem';
			if(file_exists($this->_c2dmCaFile) === false) {
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
				file_put_contents($this->_c2dmCaFile, $cert);
			}
		}
	}
	
	/**
	 * Prepare ca certificate. Usefull when certificate are not
	 * fully installed on environment
	 * 
	 * @return void
	 * @since  XXX
	 */
	private function _initApnsCa() {
		if($this->apnsEmbeddedCaFile === true) {
			$appleDir = Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'apple';
			if(is_dir($appleDir) === false) {
				mkdir($appleDir);
			}
			if($this->mode === 'production') {
				$this->_apnsCaFile = $appleDir.DIRECTORY_SEPARATOR.'apns_entrust_ca.pem';
				if(file_exists($this->_apnsCaFile) === false) {
					$cert = <<<EOC
-----BEGIN CERTIFICATE-----
MIIE2DCCBEGgAwIBAgIEN0rSQzANBgkqhkiG9w0BAQUFADCBwzELMAkGA1UEBhMC
VVMxFDASBgNVBAoTC0VudHJ1c3QubmV0MTswOQYDVQQLEzJ3d3cuZW50cnVzdC5u
ZXQvQ1BTIGluY29ycC4gYnkgcmVmLiAobGltaXRzIGxpYWIuKTElMCMGA1UECxMc
KGMpIDE5OTkgRW50cnVzdC5uZXQgTGltaXRlZDE6MDgGA1UEAxMxRW50cnVzdC5u
ZXQgU2VjdXJlIFNlcnZlciBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTAeFw05OTA1
MjUxNjA5NDBaFw0xOTA1MjUxNjM5NDBaMIHDMQswCQYDVQQGEwJVUzEUMBIGA1UE
ChMLRW50cnVzdC5uZXQxOzA5BgNVBAsTMnd3dy5lbnRydXN0Lm5ldC9DUFMgaW5j
b3JwLiBieSByZWYuIChsaW1pdHMgbGlhYi4pMSUwIwYDVQQLExwoYykgMTk5OSBF
bnRydXN0Lm5ldCBMaW1pdGVkMTowOAYDVQQDEzFFbnRydXN0Lm5ldCBTZWN1cmUg
U2VydmVyIENlcnRpZmljYXRpb24gQXV0aG9yaXR5MIGdMA0GCSqGSIb3DQEBAQUA
A4GLADCBhwKBgQDNKIM0VBuJ8w+vN5Ex/68xYMmo6LIQaO2f55M28Qpku0f1BBc/
I0dNxScZgSYMVHINiC3ZH5oSn7yzcdOAGT9HZnuMNSjSuQrfJNqc1lB5gXpa0zf3
wkrYKZImZNHkmGw6AIr1NJtl+O3jEP/9uElY3KDegjlrgbEWGWG5VLbmQwIBA6OC
AdcwggHTMBEGCWCGSAGG+EIBAQQEAwIABzCCARkGA1UdHwSCARAwggEMMIHeoIHb
oIHYpIHVMIHSMQswCQYDVQQGEwJVUzEUMBIGA1UEChMLRW50cnVzdC5uZXQxOzA5
BgNVBAsTMnd3dy5lbnRydXN0Lm5ldC9DUFMgaW5jb3JwLiBieSByZWYuIChsaW1p
dHMgbGlhYi4pMSUwIwYDVQQLExwoYykgMTk5OSBFbnRydXN0Lm5ldCBMaW1pdGVk
MTowOAYDVQQDEzFFbnRydXN0Lm5ldCBTZWN1cmUgU2VydmVyIENlcnRpZmljYXRp
b24gQXV0aG9yaXR5MQ0wCwYDVQQDEwRDUkwxMCmgJ6AlhiNodHRwOi8vd3d3LmVu
dHJ1c3QubmV0L0NSTC9uZXQxLmNybDArBgNVHRAEJDAigA8xOTk5MDUyNTE2MDk0
MFqBDzIwMTkwNTI1MTYwOTQwWjALBgNVHQ8EBAMCAQYwHwYDVR0jBBgwFoAU8Bdi
E1U9s/8KAGv7UISX8+1i0BowHQYDVR0OBBYEFPAXYhNVPbP/CgBr+1CEl/PtYtAa
MAwGA1UdEwQFMAMBAf8wGQYJKoZIhvZ9B0EABAwwChsEVjQuMAMCBJAwDQYJKoZI
hvcNAQEFBQADgYEAkNwwAvpkdMKnCqV8IY00F6j7Rw7/JXyNEwr75Ji174z4xRAN
95K+8cPV1ZVqBLssziY2ZcgxxufuP+NXdYR6Ee9GTxj005i7qIcyunL2POI9n9cd
2cNgQ4xYDiKWL2KjLB+6rQXvqzJ4h6BUcxm1XAX5Uj5tLUUL9wqT6u0G+bI=
-----END CERTIFICATE-----
EOC;
					file_put_contents($this->_apnsCaFile, $cert);
				}
			} else {
				$this->_apnsCaFile = $appleDir.DIRECTORY_SEPARATOR.'apns_entrust_sandbox_ca.pem';
				if(file_exists($this->_apnsCaFile) === false) {
					$cert = <<<EOC
-----BEGIN CERTIFICATE-----
MIIEKjCCAxKgAwIBAgIEOGPe+DANBgkqhkiG9w0BAQUFADCBtDEUMBIGA1UEChML
RW50cnVzdC5uZXQxQDA+BgNVBAsUN3d3dy5lbnRydXN0Lm5ldC9DUFNfMjA0OCBp
bmNvcnAuIGJ5IHJlZi4gKGxpbWl0cyBsaWFiLikxJTAjBgNVBAsTHChjKSAxOTk5
IEVudHJ1c3QubmV0IExpbWl0ZWQxMzAxBgNVBAMTKkVudHJ1c3QubmV0IENlcnRp
ZmljYXRpb24gQXV0aG9yaXR5ICgyMDQ4KTAeFw05OTEyMjQxNzUwNTFaFw0yOTA3
MjQxNDE1MTJaMIG0MRQwEgYDVQQKEwtFbnRydXN0Lm5ldDFAMD4GA1UECxQ3d3d3
LmVudHJ1c3QubmV0L0NQU18yMDQ4IGluY29ycC4gYnkgcmVmLiAobGltaXRzIGxp
YWIuKTElMCMGA1UECxMcKGMpIDE5OTkgRW50cnVzdC5uZXQgTGltaXRlZDEzMDEG
A1UEAxMqRW50cnVzdC5uZXQgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkgKDIwNDgp
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArU1LqRKGsuqjIAcVFmQq
K0vRvwtKTY7tgHalZ7d4QMBzQshowNtTK91euHaYNZOLGp18EzoOH1u3Hs/lJBQe
sYGpjX24zGtLA/ECDNyrpUAkAH90lKGdCCmziAv1h3edVc3kw37XamSrhRSGlVuX
MlBvPci6Zgzj/L24ScF2iUkZ/cCovYmjZy/Gn7xxGWC4LeksyZB2ZnuU4q941mVT
XTzWnLLPKQP5L6RQstRIzgUyVYr9smRMDuSYB3Xbf9+5CFVghTAp+XtIpGmG4zU/
HoZdenoVve8AjhUiVBcAkCaTvA5JaJG/+EfTnZVCwQ5N328mz8MYIWJmQ3DW1cAH
4QIDAQABo0IwQDAOBgNVHQ8BAf8EBAMCAQYwDwYDVR0TAQH/BAUwAwEB/zAdBgNV
HQ4EFgQUVeSB0RGAvtiJuQijMfmhJAkWuXAwDQYJKoZIhvcNAQEFBQADggEBADub
j1abMOdTmXx6eadNl9cZlZD7Bh/KM3xGY4+WZiT6QBshJ8rmcnPyT/4xmf3IDExo
U8aAghOY+rat2l098c5u9hURlIIM7j+VrxGrD9cv3h8Dj1csHsm7mhpElesYT6Yf
zX1XEC+bBAlahLVu2B064dae0Wx5XnkcFMXj0EyTO2U87d89vqbllRrDtRnDvV5b
u/8j72gZyxKTJ1wDLW8w0B62GqzeWvfRqqgnpv55gcR5mTNXuhKwqeBCbJPKVt7+
bYQLCIt+jerXmCHG8+c8eS9enNFMFY3h7CI3zJpDC5fcgJCNs2ebb0gIFVbPv/Er
fF6adulZkMV8gzURZVE=
-----END CERTIFICATE-----
EOC;
					file_put_contents($this->_apnsCaFile, $cert);
				}
			}
		}
	}
}

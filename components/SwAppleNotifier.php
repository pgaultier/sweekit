<?php
/**
 * SwAppleNotififier.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweekit.components
 */

/**
 * This SwAppleNotififier allow users to send
 * notification to iOs devices.
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  components
 * @package   sweekit.components
 *
 * @property string $certificatePath
 * @property string $temporaryCertificatePath
 */
class SwAppleNotifier extends CComponent implements SwMobileNotifierInterface {
	/**
	 * Apple constants
	 */
	const APNS_PUSH_URL = 'ssl://gateway.push.apple.com:2195';
	const APNS_SANDBOX_PUSH_URL = 'ssl://gateway.sandbox.push.apple.com:2195';
	const APNS_FEEDBACK_URL = 'ssl://feedback.push.apple.com:2196';
	const APNS_SANDBOX_FEEDBACK_URL = 'ssl://feedback.sandbox.push.apple.com:2196';
	const APNS_RUNTIME_PATH = 'application.runtime.ios';
	const APNS_CERTIFICATES_PATH = 'application.config.certificates';
	const APNS_SOCKET_TIMEOUT = 2;
	const APNS_READ_ERROR_INTERVAL = 10000;
	const APNS_WRITE_INTERVAL = 10000;
	const DIRECTORY_CREATE_MASK = 0777;

	private $_certificatePath;

	/**
	 * Define the certificates path in Yii pathalias form
	 *
	 * @param string $certificatePath
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setCertificatePath($certificatePath) {
		$this->_certificatePath = Yii::getPathOfAlias($certificatePath).DIRECTORY_SEPARATOR;
	}

	/**
	 * Get current certificate path. This method
	 * returns a real path
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getCertificatePath() {
		if($this->_certificatePath === null) {
			$this->_certificatePath = Yii::getPathOfAlias(self::APNS_CERTIFICATES_PATH).DIRECTORY_SEPARATOR;
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
	 * @since  XXX
	 */
	public function setTemporaryCertificatePath($certificatePath) {
		$this->_temporaryCertificateFile = Yii::getPathOfAlias($certificatePath).DIRECTORY_SEPARATOR;
	}

	/**
	 * Get current certificate path. This method
	 * returns a real path
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getTemporaryCertificatePath() {
		if($this->_temporaryCertificateFile === null) {
			$this->_temporaryCertificateFile = Yii::getPathOfAlias(self::APNS_RUNTIME_PATH).DIRECTORY_SEPARATOR;
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
	public $certificateFile;

	/**
	 * @var string name of the key file in p12 format
	 */
	public $keyFile;

	/**
	 * @var string name of the bundle file in PEM format
	 */
	public $bundleFile;

	/**
	 * @var string passphrase to open pkcs12 bundle
	 */
	public $passphrase;

	/**
	 * @var string name of the certificate file in DER format (direct from apple)
	 */
	public $caFile;

	/**
	 * @var boolean set to true if we have to embed the cafile (usefull when ssl dir is outdated)
	 */
	public $embeddedCaFile=false;

	private $_bundleFile;

	/**
	 * Create the bundle file if needed
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function findBundleFile() {
		if($this->_bundleFile === null) {
			if(($this->certificateFile === null) && ($this->keyFile === null) && ($this->bundleFile !== null) && (is_file($this->getCertificatePath().$this->bundleFile) === true)) {
				// we have a bundle
				$this->_bundleFile = $this->getCertificatePath().$this->bundleFile;
			} elseif((is_file($this->getCertificatePath().$this->certificateFile) === true) && (is_file($this->getCertificatePath().$this->keyFile) === true)) {
				// we have to build the bundle
				$bundleName = sprintf('%x',crc32($this->certificateFile.$this->keyFile)).'.pem';
				if(is_file($this->getTemporaryCertificatePath().$bundleName) === true) {
					$this->_bundleFile = $this->getTemporaryCertificatePath().$bundleName;
				} else {
					$this->_bundleFile = $this->buildBundleFile($this->getTemporaryCertificatePath(), $bundleName);
				}
			}
		}
		return $this->_bundleFile;
	}

	/**
	 * Rebuild the whole bundle file
	 *
	 * @param string $bundlePath path to store the bundle
	 * @param string $bundleName filename for the created bundle
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function buildBundleFile($bundlePath, $bundleName) {
		$data = null;
		if(openssl_pkcs12_read(file_get_contents($this->getCertificatePath().$this->keyFile), $data, $this->passphrase)) {
			$pemKeyFile = null;
			if(openssl_pkey_export($data['pkey'], $pemKeyFile, $this->passphrase) === true) {
				$bundleCert = "-----BEGIN CERTIFICATE-----\n";
				$bundleCert .= chunk_split(base64_encode(file_get_contents($this->getCertificatePath().$this->certificateFile)), 64, "\n");
				$bundleCert .= "-----END CERTIFICATE-----\n";
				$bundleCert .= $pemKeyFile;
				if(is_dir($bundlePath) === false) {
					if(mkdir($bundlePath, self::DIRECTORY_CREATE_MASK, true) === false) {
						throw new CException('Directory cannot be created');
					}
				}
				$result = file_put_contents($bundlePath.$bundleName, $bundleCert);
			} else {
				throw new CException('OpenSSL error : '.openssl_error_string());
			}
		} else {
			throw new CException('OpenSSL error : '.openssl_error_string());
		}
		return $bundlePath.$bundleName;
	}


	/**
	 * Generate a binary notification from a device token and a JSON-encoded payload.
	 *
	 * @see http://tinyurl.com/ApnsBinaryDetails
	 *
	 * @param string  $deviceId  device token.
	 * @param string  $payload   JSON-encoded payload.
	 * @param array   $messageId message unique ID.
	 * @param integer $expire    seconds, starting from now
	 *
	 * @return string
	 * @since  XXX
	 */
	protected function prepareBinaryNotification($deviceId, $payload, $parameters) {
		$payloadLength = strlen($payload);
		if(isset($parameters['expire']) === true) {
			$messageHeader = pack('CNNnH64n', 1, $parameters['messageId'], $parameters['expire'], 32, $deviceId, $payloadLength);
		} else {
			$messageHeader = pack('CnH64n', 0, 32, $deviceId, $payloadLength);
		}
		return $messageHeader.$payload;
	}

	private $_currentMessageId=0;
	private $_messageQueue = array();

	/**
	 * Prepare one or more messages
	 * @see SwMobileNotifierInterface::prepare()
	 *
	 *
	 * @param mixed $deviceIds  string if one device is the target else an array with the list of all targets
	 * @param array $payload    an array which contains all the data to send. @see http://tinyurl.com/ApnsNotificationPayload
	 * @param array $parameters an array of extended parameters (expire, messageId). @see http://tinyurl.com/ApnsBinaryDetails
	 */
	public function prepare($deviceIds, $payload, $parameters=array()) {
		if(is_array($deviceIds) === false) {
			$deviceIds = array($deviceIds);
		}
		$jsonPayload = CJSON::encode($payload);
		foreach($deviceIds as $deviceId) {
			$filteredParameters['messageId'] = (++$this->_currentMessageId);
			if(isset($parameters['expire']) === true) {
				$filteredParameters['expire'] =  (($parameters['expire']>0)?(time()+$parameters['expire']):0) ;
			}
			$this->_messageQueue[$filteredParameters['messageId']] = $this->prepareBinaryNotification($deviceId, $jsonPayload, $filteredParameters);
		}
	}
	private $_sentQueue=array();

	/**
	 * Send the notifications
	 *
	 * @return void
	 * @since  XXX
	 */
	public function notify() {
		foreach($this->_messageQueue as $messageId => $binaryMessage) {
			fwrite($this->getSocket(), $binaryMessage);
			// unset($this->_messageQueue[$messageId]);
			usleep(self::APNS_WRITE_INTERVAL);
			$this->_sentQueue[$messageId] = array(
				'success' => true,
				'code' => -1,
				'messageId' => $messageId
			);
		}
		usleep(self::APNS_READ_ERROR_INTERVAL);
		while(!feof($this->getSocket())) {
			$errorData = fread($this->getSocket(), 6);
			usleep(self::APNS_READ_ERROR_INTERVAL);
			// set status as ok
			if(($errorData !== null) && (strlen($errorData) === 6)) {
				// we probably have an error
				$error = unpack('CcommandId/CstatusCode/NmessageId', $errorData);
				if((isset($error['commandId']) === true) && ($error['commandId'] === 8)) {
					// we have something returned
					$this->_sentQueue[$error['messageId']] = array(
						'success' => ($error['statusCode']==0),
						'code' => $error['statusCode'],
						'messageId' => $error['messageId']
					);
				}
			}
		}

		$this->disconnect();
	}

	/**
	 * Get status for current queue.
	 *
	 * @see SwMobileNotifierInterface::getStatus()
	 *
	 * @return array
	 * @since  XXX
	 */
	public function getStatus() {
		return $this->_sentQueue;
	}

	private $_connected = false;
	private $_socket;

	/**
	 * Get current socket
	 * open a socket if needed
	 *
	 * @return resource
	 * @since  XXX
	 */
	public function getSocket() {
		if($this->_socket === null) {
			$this->connect();
		}
		return $this->_socket;
	}

	/**
	 * Connect to apple socker
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function connect() {
		if($this->_connected === false) {
			$streamContext = stream_context_create();
			stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->findBundleFile());
			if($this->passphrase !== null) {
				stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->passphrase);
			}
			if($this->embeddedCaFile === true) {
				stream_context_set_option($streamContext, 'ssl', 'cafile', $this->findCaFile());
			}
			$url = ($this->isProduction === true)?self::APNS_PUSH_URL:self::APNS_SANDBOX_PUSH_URL;
			$this->_socket = stream_socket_client($url, $errorNumber, $errorString, self::APNS_SOCKET_TIMEOUT, STREAM_CLIENT_CONNECT, $streamContext);
			stream_set_blocking($this->_socket, 0);
			stream_set_write_buffer($this->_socket, 0);
			if($this->_socket === false) {
				throw new CException('Unable to open socket ('.$errorNumber.') : '.$errorString);
			}
			$this->_connected = true;
		}
		return $this->_connected;
	}

	/**
	 * Close current socket
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function disconnect() {
		if($this->_connected === true) {
			fclose($this->_socket);
			$this->_connected = false;
		}
	}

	private $_caFile;

	/**
	 * Find correct real path for CA file. only used when embbed CA file is active
	 *
	 * @return string
	 * @since  XXX
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
	 * @since  XXX
	 */
	protected function embbedCaFile() {
		if($this->isProduction === true) {
			$tempCaFile = $this->getTemporaryCertificatePath().'apns_entrust_ca.pem';
		} else {
			$tempCaFile = $this->getTemporaryCertificatePath().'apns_entrust_sandbox_ca.pem';
		}
		if(is_file($tempCaFile) === false) {
			if(is_dir($this->getTemporaryCertificatePath()) === false) {
				mkdir($this->getTemporaryCertificatePath(), self::DIRECTORY_CREATE_MASK, true);
			}
			if($this->isProduction === true) {
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
			} else {
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
			}
			// we should create it
			file_put_contents($tempCaFile, $cert);
		}
		return $tempCaFile;
	}
}

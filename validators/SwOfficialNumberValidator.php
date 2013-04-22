<?php
/**
 * File SwOfficialNumberValidator.php
*
* PHP version 5.2+
*
* @author    Philippe Gaultier <pgaultier@sweelix.net>
* @copyright 2010-2013 Sweelix
* @license   http://www.sweelix.net/license license
* @version   XXX
* @link      http://www.sweelix.net
* @category  validators
* @package   sweekit.validators
*/

/**
 * Class SwOfficialNumberValidator allow easy validation
 * of official numbers such as BBAN, IBAN, ...
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  validators
 * @package   sweekit.validators
 * @since     XXX
*/
class SwOfficialNumberValidator extends CValidator {

	/**
	 * @var array list of available sub validators
	 */
	public static $officialNumberValidators = array(
		'iban' => 'iso13616',
		'visa' => 'iso7812',
		'mastercard' => 'iso7812',
		'amex' => 'iso7812',
		'siren' => 'iso7812',
		'siret' => 'iso7812',
		'rib' => 'rib',
		'vat' => 'vat',
		'insee' => 'modulo97',
	);

	/**
	 * @var string type of element to validate
	 */
	public $type;

	/**
	 * Check if current attribute value conform to selected
	 * algorithm
	 *
	 * @param CModel $object    object owner of the property
	 * @param string $attribute attribute to validate
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function validateAttribute($object, $attribute) {

		if(isset(self::$officialNumberValidators[$this->type]) === true) {

			$method = 'check'.ucfirst(self::$officialNumberValidators[$this->type]);
			$value = $object->$attribute;

			$result = call_user_func(array($this, $method), $value);

			if($result === false) {
				$message=$this->message!==null?$this->message:Yii::t('sweelix','{attribute} is not a valid {type}.');
				$this->addError($object,$attribute,$message,array('{type}'=>$this->type));
			}
		} else {
			throw new CException(Yii::t('sweelix','type {type} is incorrect.', array('{type}'=>$this->type)));
		}


	}

	/**
	 * Compute the iso 7812 modulus also known as Luhn or
	 * mod 10 algorithm
	 *
	 * @param string $number number to validate
	 *
	 * @return integer
	 * @since  XXX
	 */
	protected function computeIso7812Modulus($number) {
		$sumTable = array(
			array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
			array(0, 2, 4, 6, 8, 1, 3, 5, 7, 9),
		);
		$result = 0;
		$parity = 0;
		for($i=(strlen($number) - 1); $i>=0; $i--) {
			$result += $sumTable[$parity][$number[$i]];
			$parity = 1 - $parity;
		}
		$modulus = $result % 10;
		return $modulus;
	}

	/**
	 * Check if number conform to iso 7812 validation algorithm
	 * also know as Luhn or mod 10 algorithm
	 *
	 * @param string $number number to validate
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function checkIso7812($number) {
		$check = false;
		if(preg_match('/^([0-9]+)$/i', $number) === 1) {
			$check = ($this->computeIso7812Modulus($number) === 0);
		}
		return $check;
	}

	/**
	 * Check if number conform to iso 13616 validation algorithm
	 *
	 * @param string $number number to validate
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function checkIso13616($number) {
		$check = false;
		if(preg_match('/^([a-z0-9]+)$/i', $number) === 1) {
			$number = strtoupper($number);
			$number = substr($number,4,strlen($number)-4).substr($number,0,4);
			$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
			$numbers = array( 10,  11,  12,  13,  14,  15,  16,  17,  18,  19,  20,  21,  22,  23,  24,  25,  26,  27,  28,  29,  30,  31,  32,  33,  34,  35);
			$tmpNumber = str_replace($letters, $numbers, $number);
			$modulus = bcmod($tmpNumber, 97);
			$check = ($modulus == 1);
		}
		return $check;
	}

	/**
	 * Check if number conform to RIB validation algorithm
	 *
	 * @param string $number number to validate
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function checkRib($number) {
		$check = false;
		if(preg_match('/^([a-z0-9]+)$/i', $number) === 1) {
			$number = strtoupper($number);
			$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
			$numbers = array(  1,   2,   3,   4,   5,   6,   7,   8,   9,   1,   2,   3,   4,   5,   6,   7,   8,   9,   2,   3,   4,   5,   6,   7,   8,   9);
			$tmpNumber = str_replace($letters, $numbers, $number);
			$bank = bcmul(89, substr($tmpNumber, 0, 5));
			$office = bcmul(15, substr($tmpNumber, 5, 5));
			$account = bcmul(3, substr($tmpNumber, 10, 11));
			$key = substr($tmpNumber, 21, 2);
			$tmpNumber = bcadd(bcadd(bcadd($bank, $office), $account), $key);
			$modulus = bcmod($tmpNumber, 97);
			$check = ($modulus == 0);
		}
		return $check;
	}

	/**
	 * Check if number conform to mod 97 validation algorithm
	 *
	 * @param string $number number to validate
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function checkModulo97($number) {
		$check = false;
		$matches = array();
		if(preg_match('/^([0-9]+)([0-9]{2})$/', $number, $matches) == 1) {
			$modulo = 97 - bcmod($matches[1], 97);
			$check = ($modulo == $matches[2]);
		}
		return $check;
	}

	/**
	 * Check if VAT number is correct using FR rules
	 *
	 * @param string $number vat number
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function checkFrVat($number) {
		$check = false;
		$key = substr($number, 2, 2);
		$siren = substr($number, 4, 9);
		$check = $this->checkIso7812($siren);
		if($check === true) {
			$checkKey = (12 + 3 * ($siren % 97)) % 97;
			$ckeck = ($key == $checkKey);
		}
		return $check;
	}

	/**
	 * Check if VAT number is correct using BE rules
	 *
	 * @param string $number vat number
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function checkBeVat($number) {
		return $this->checkModulo97(substr($number, 2, 10));
	}

	/**
	 * Check if VAT number is correct
	 *
	 * @param string $number vat number
	 *
	 * @return boolean
	 * @since  XXX
	 */
	protected function checkVat($number) {
		$check = false;
		$countryCode = substr($number, 0, 2);
		$vatValidator = $this->findVatValidator($countryCode);
		if($vatValidator !== false) {
			$check = call_user_func(array($this, $vatValidator), $number);
		} else {
			throw new CException(Yii::t('sweelix','VAT Validator not implemented for country "{country}".', array('{country}'=>$countryCode)));
		}
		return $check;
	}

	/**
	 * Find VAT validator using the country code.
	 * Return method name if validator exists
	 *
	 * @param string $country country code
	 *
	 * @return string
	 * @since  XXX
	 */
	protected function findVatValidator($country) {
		$vatChecker = 'check'.ucfirst(strtolower($country)).'Vat';
		if(method_exists($this, $vatChecker) === false) {
			$vatChecker = false;
		}
		return $vatChecker;
	}

}
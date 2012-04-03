<?php
/**
 * DemoForm.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  models
 * @package   Sweeml.samples.models
 */

/**
 * 
 * Model to demo ajax stuff
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.models
 * @since     XXX
 */
class DemoForm extends CFormModel {
	public $login;
	
	/**
	 * rules to apply to the model
	 * @see CModel::rules()
	 * 
	 * @return array
	 * @since  XXX
	 */
	public function rules() {
		return array(
			array('login', 'length', 'min' => 4, 'max' => 12),
			array('login', 'required'),
		);
	}
}
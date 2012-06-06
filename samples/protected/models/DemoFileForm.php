<?php
/**
 * DemoFileForm.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  models
 * @package   Sweeml.samples.models
 */

/**
 * 
 * Model used to perform tests on plupload
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.models
 * @since     1.9.0
 */
 class DemoFileForm extends CFormModel {
	public $file;
	
	/**
	 * rules to apply to the model
	 * @see CModel::rules()
	 * 
	 * @return array
	 * @since  1.9.0
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('file', 'ext.sweekit.validators.SwFileValidator', 'maxFiles' => 2, 'allowEmpty' => true),
		);
	}
}
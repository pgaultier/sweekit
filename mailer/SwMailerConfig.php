<?php
/**
 * File SwMailerConfig.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  mailer
 * @package   sweekit.mailer
 */

Yii::import('ext.sweekit.mailer.SwMailerInterface');

/**
 * Class SwMailerConfig allow configuration of selected mailer
 *
 * component must be called mailer.
 *
 * <code>
 *  'mailer' => array(
 *  	'class' => 'ext.sweekit.mailer.SwMailerConfig',
 *  	'parameters' => array(
 *  		'class' => 'ext.sweekit.mailer.SwMailerCritsend',
 *  		'apiUsername' => 'user name',
 *  		'apiPassword' => 'user password'
 *  	)
 *  )
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  mailer
 * @package   sweekit.mailer
 * @since     XXX
 */
 class SwMailerConfig extends CApplicationComponent {
 	/**
 	 * @var boolean define status of the module
 	 */
 	private $_initialized = false;

 	/**
 	 * @var array Configuration parameters
 	 */
 	private $_subConfig;

 	/**
 	 * Define selected object class using classic component configuration
 	 *
 	 * @param array $parameters
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function setParameters($parameters) {
 			if($this->_initialized === true) {
			throw new CException(Yii::t('sweelix', 'SwMailerConfig, parameters can be defined only during configuration'));
		}
 		$this->_subConfig = $parameters;
 	}

 	/**
 	 * Get current configuration
 	 *
 	 * @return array
 	 * @since  XXX
 	 */
 	public function getParameters() {
 		return $this->_subConfig;
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

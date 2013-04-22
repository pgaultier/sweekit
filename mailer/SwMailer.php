<?php
/**
 * SwMailer.php
 *
 * PHP version 5.3+
 *
 *
 *
 * @author    Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright 2010-2013 Ibitux
 * @license   http://www.ibitux.com/license license
 * @version   XXX
 * @link      http://code.ibitux.net/projects/
 * @category
 * @package   application.
 */

 class SwMailer {

 	private static $_mailerInstance;

 	/**
 	 * Disallow constructor access
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function __construct() {
		Yii::log(Yii::t('sweelix', '{object} cannot be created using new', array('{object}'=>'SwMailer')), CLogger::LEVEL_ERROR, 'ext.sweekit.mailer');
		throw new CException(Yii::t('sweelix', '{object},must be created using getInstance()', array('{object}'=>'SwMailer')));
 	}

	/**
	 * Create / Return the singleton for current mailing
	 * system
	 *
	 * @return SwMailer
	 * @since  XXX
	 */
 	public static function getInstance() {
		if(self::$_mailerInstance === null) {
			$module = Yii::app()->getComponent('mailer');
			if($module === null) {
				Yii::log(Yii::t('sweelix', '{object} has not been defined', array('{object}'=>'SwMailerConfig')), CLogger::LEVEL_ERROR, 'ext.sweekit.mailer');
				throw new CException(Yii::t('sweelix', 'SwMailerConfig, component has not been defined'));
			}
			$parameters = $module->getParameters();
			self::$_mailerInstance = Yii::createComponent($parameters);
			if((self::$_mailerInstance instanceof SwMailerInterface) === false) {
				throw new CException(Yii::t('sweelix', '{class}, must implement SwMailerInterface', $parameters['class']));
			}
		}
		return self::$_mailerInstance;
 	}

 	/**
 	 * Forwarder
 	 *
 	 * @param string $name      method name
 	 * @param array  $arguments arguments passed to selected method
 	 *
 	 * @return mixed
 	 * @since  XXX
 	 */
 	public function __call($name, $arguments) {
		if(method_exists(self::$_mailerInstance, $name) === true) {
			return call_user_func_array(array(self::$_mailerInstance, $name), $arguments);
		} else {
			throw new CException(Yii::t('sweelix', 'Object {object} does not feature method {method}', array('{object}' => get_class(self::$_mailerInstance), '{method}' => $name)));
		}
 	}
 }

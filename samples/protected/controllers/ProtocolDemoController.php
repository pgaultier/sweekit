<?php
/**
 * ProtocolDemoController.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.9.0
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.controllers
 */

/**
 *
 * This demo need a valid certificate (https) for your server 
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.9.0
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.controllers
 * @since     1.9.0
 */
class ProtocolDemoController extends CController {
	
	/**
	 * This page stays with current mode (http/https)
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionIndex() {
		$this->render('index');
	}
	
	/**
	 * This page renders in https only
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionSecured() {
		$this->render('index');
	}
	
	/**
	 * This page renders in http only
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionClassic() {
		$this->render('index');
	}
	
	/**
	 * Add filters to current controller
	 *
	 * @return array
	 */
	public function filters() {
		return array(
				array(
						'ext.sweekit.filters.SwProtocolFilter + secured',
						'mode' => 'https',
				),
				array(
						'ext.sweekit.filters.SwProtocolFilter + classic',
						'mode' => 'http',
				),
		);
	}
}
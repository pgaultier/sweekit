<?php
/**
 * SiteController.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.controllers
 */

/**
 * 
 * This is only the entry point to different elements
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.controllers
 * @since     1.9.0
 */
class SiteController extends CController {
	
	/**
	 * Default action
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionIndex() {
		$this->render('index');
	}
}
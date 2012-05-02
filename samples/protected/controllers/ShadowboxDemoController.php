<?php
/**
 * ShadowboxDemoController.php
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
 * This demo cannot work if sweelix clientscript behavior
 * is not attached.
 * 
 * @see config/main.php where the behavior is attached using following 
 * statement
 * 'clientScript' => array(
 *   'behaviors' => array(
 *     'sweelixClientScript' => array(
 *       'class' => 'ext.sweekit.behaviors.SwClientScriptBehavior',
 *     ),
 *   ),
 * ),	
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
class ShadowboxDemoController extends CController {
	/**
	 * Attach behaviors to current controller
	 * @see CController::behaviors()
	 * 
	 * @return array
	 * @since  1.9.0
	 */
	public function behaviors() {
		return array(
			/**
			 * Attach Rendering behaviors to utility methods (usefull with ajax calls)
			 *  * redirectJs($url, $timer=null, $terminate=true)
			 *  * renderJs($script, $terminate=true)
			 *  * renderJson($data, $httpCode=200, $terminate=true)
			 *  
			 */
			'sweelixRendering' => array(
				'class' => 'ext.sweekit.behaviors.SwRenderBehavior',
			),
		);
	}
	
	/**
	 * Default action
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionIndex() {
		$this->render('index');
	}
	
	/**
	 * render content for the shadowbox
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionDisplayInfo() {
		$this->render('info');
	}
	
}
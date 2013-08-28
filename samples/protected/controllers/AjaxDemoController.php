<?php
/**
 * AjaxDemoController.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.11.0
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
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.11.0
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.controllers
 * @since     1.9.0
 */
class AjaxDemoController extends CController {
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
		$demoForm = new DemoForm();
		$this->render('index', array('demoForm' => $demoForm));
	}
	
	/**
	 * render only the bloc content to refresh
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionBlocRefresh() {
		$this->renderPartial('_blocRefresh');
	}
	
	/**
	 * Render javascript code which should be expended
	 * client side
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionJavascriptRefresh() {
		$jsCode = "alert('Refresh was performed @ ".date('d/m/Y h:i:s')." ');";
		$this->renderJs($jsCode);
	}
	
	/**
	 * Form was submited, and should be rendered 
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionFormSubmit() {
		$demoForm = new DemoForm();
		if(isset($_POST['DemoForm']) === true) {
			$demoForm->attributes = $_POST['DemoForm'];
			if($demoForm->validate()) {
				// we use javascript redirect to be sure redirect
				// will be performed even during ajax calls
				$this->redirectJs(array('formValid'));
			}
		}
		if(Yii::app()->getRequest()->getIsAjaxRequest() === true) {
			// ajax request so we have to partial update
			$this->render('_form', array('demoForm' => $demoForm));
		} else {
			$this->render('index', array('demoForm' => $demoForm));
		}
	}
	
	/**
	 * Render page to view when for has been
	 * validated
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionFormValid() {
		$this->render('formValid');
	}
}
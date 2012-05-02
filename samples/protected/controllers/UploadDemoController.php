<?php
/**
 * UploadDemoController.php
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
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.9.0
 * @link      http://www.sweelix.net
 * @category  controllers
 * @package   Sweeml.samples.controllers
 * @since     1.9.0
 */
class UploadDemoController extends CController {
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
			 */
			'sweelixRendering' => array(
				'class' => 'ext.sweekit.behaviors.SwRenderBehavior',
			),
		);
	}
	/**
	 * Attach actions needed to handle plupload
	 * @see CController::actions()
	 * 
	 * @return array
	 * @since  1.9.0
	 */
	public function actions() {
		return array(
				'asyncUpload' => 'ext.sweekit.actions.SwUploadAction',
				'asyncDelete' => 'ext.sweekit.actions.SwDeleteAction',
		);
	}
	
	/**
	 * This page stays with current mode (http/https)
	 * 
	 * @return void
	 * @since  1.9.0
	 */
	public function actionIndex() {
		$demoFileForm = new DemoFileForm();
		$savedFiles = false;
		if(isset($_POST['DemoFileForm']) == true) {
			Yii::import('ext.sweekit.web.SwUploadedFile');
			$demoFileForm->file = SwUploadedFile::getInstances($demoFileForm, 'file');
			if($demoFileForm->validate()) {
				// we have to handle the file field as an array be cause multi upload was activated
				$savedFiles = array();
				foreach($demoFileForm->file as $i => $file) {
					$savedFiles[] = $targetSavePath = 'files/'.$file->getName();
					$file->saveAs($targetSavePath);
				}
			}
		}		
		$this->render('index', array('demoFileForm' => $demoFileForm, 'savedFiles' => $savedFiles));
	}
}
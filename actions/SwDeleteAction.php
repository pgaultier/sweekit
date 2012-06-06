<?php
/**
 * SwDeleteAction.php
 * 
 * PHP version 5.2+
 * 
 * Action
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  actions
 * @package   Sweeml.actions
 */	
Yii::import('ext.sweekit.web.SwUploadedFile');
/**
 * This SwDeleteAction handle the xhr /swfupload process
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  actions
 * @package   Sweeml.actions
 * @since     1.1
 */	
class SwDeleteAction extends CAction {
	/**
	 * Run the action and perform the delete process
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function run() {
		Yii::trace(__CLASS__.'::'.__FUNCTION__.'()', 'Sweeml.actions');
		try {
			$sessionId = Yii::app()->getSession()->getSessionId();
			$fileName = Yii::app()->getRequest()->getParam('name', '');
			$id = Yii::app()->getRequest()->getParam('id', 'unk');
			$targetPath = Yii::getPathOfAlias(SwUploadedFile::$targetPath).DIRECTORY_SEPARATOR.$sessionId.DIRECTORY_SEPARATOR.$id;
			$response = array('fileName' => $fileName, 'status' => false, 'fileSize' => null);
			if((file_exists($targetPath.DIRECTORY_SEPARATOR.$fileName) == true) && (is_file($targetPath.DIRECTORY_SEPARATOR.$fileName) == true)) {
				unlink($targetPath.DIRECTORY_SEPARATOR.$fileName);
				$response['status'] = true;
			}
			if(Yii::app()->request->isAjaxRequest == true) {
				$this->getController()->renderJson($response);
			} else {
				echo CJSON::encode($response);
			}
		}
		catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'Sweeml.actions');
			throw $e;
		}
	}
}
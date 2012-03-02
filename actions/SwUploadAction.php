<?php
/**
 * SwUploadAction.php
 * 
 * PHP version 5.2+
 * 
 * Action
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.6.2
 * @link      http://www.sweelix.net
 * @category  actions
 * @package   Sweeml.actions
 */	
Yii::import('ext.sweekit.web.SwUploadedFile');
/**
 * This SwUploadAction handle the xhr /swfupload process
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.6.2
 * @link      http://www.sweelix.net
 * @category  actions
 * @package   Sweeml.actions
 * @since     1.1
 */	
class SwUploadAction extends CAction {
	/**
	 * Run the action and perform the upload process
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function run() {
		Yii::trace(__CLASS__.'::'.__FUNCTION__.'()', 'Sweeml.actions');
		try {
			$chunk = Yii::app()->getRequest()->getParam('chunk', 0);
			$chunks = Yii::app()->getRequest()->getParam('chunks', 0);
			$fileName = Yii::app()->getRequest()->getParam('name', '');
			$sessionId = Yii::app()->getRequest()->getParam('key', Yii::app()->getSession()->getSessionId());
			$id = Yii::app()->getRequest()->getParam('id', 'unk');
			$targetPath = Yii::getPathOfAlias(SwUploadedFile::$targetPath).DIRECTORY_SEPARATOR.$sessionId.DIRECTORY_SEPARATOR.$id;

			// TODO: Clean the fileName for security reasons
			// $fileName = preg_replace('/[^\w\._]+/', '', $fileName);
			
			if(is_dir($targetPath) == false) {
				mkdir($targetPath, 0777, true);
			}
			
			// create unique fileName only if chunking is disabled
			if (($chunks < 2) && (file_exists($targetPath . DIRECTORY_SEPARATOR . $fileName) == true)) {
				$fileNameInfo = pathinfo($fileName);
				$count = 1;
				while (file_exists($targetPath . DIRECTORY_SEPARATOR . $fileNameInfo['filename'] . '_' . $count . '.' . $fileNameInfo['extension'])) {
					$count++;
				}
				$fileName = $fileNameInfo['filename'] . '_' . $count . '.' . $fileNameInfo['extension'];
			}
			$response = array('fileName' => $fileName, 'status' => true, 'fileSize' => null);
			// Look for the content type header
			$contentType = null;
			if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
				$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
			}
			if (isset($_SERVER["CONTENT_TYPE"]) == true) {
				$contentType = $_SERVER["CONTENT_TYPE"];
			}
			if (strpos($contentType, "multipart") !== false) {
				if ((isset($_FILES['file']['tmp_name']) == true) && (is_uploaded_file($_FILES['file']['tmp_name']) == true)) {
					// Open temp file
					$out = fopen($targetPath . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
					if ($out !== false) {
						// Read binary input stream and append it to temp file
						$in = fopen($_FILES['file']['tmp_name'], "rb");
						if ($in !== false) {
							while ($buff = fread($in, 4096)) {
								fwrite($out, $buff);
							}
						} else {
							$response['status'] = false;
						}
						fclose($in);
						fclose($out);
						@unlink($_FILES['file']['tmp_name']);
						$response['fileSize'] = filesize($targetPath . DIRECTORY_SEPARATOR . $fileName);
					} else {
						$response['status'] = false;
					}
				} else {
					$response['status'] = false;
					
				}
			} else {
				// Open temp file
				$out = fopen($targetPath . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out !== false) {
					// Read binary input stream and append it to temp file
					$in = fopen("php://input", "rb");
					if ($in !== false) {
						while ($buff = fread($in, 4096)) {
							fwrite($out, $buff);
						}
						$response['fileSize'] = filesize($targetPath . DIRECTORY_SEPARATOR . $fileName);
					} else {
						$response['status'] = false;
					}
					fclose($in);
					fclose($out);
				} else {
					$response['status'] = false;
				}
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
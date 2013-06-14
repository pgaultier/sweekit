<?php
/**
 * SwPreviewAction.php
 *
 * PHP version 5.2+
 *
 * Action
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  actions
 * @package   Sweeml.actions
 */

Yii::import('ext.sweekit.web.SwUploadedFile');
Yii::import('ext.sweekit.web.SwImage');
Yii::import('ext.sweekit.web.SwCacheImage');

/**
 * This SwPreviewAction handle the xhr / swfupload process for preview
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  actions
 * @package   Sweeml.actions
 * @since     XXX
 */
class SwPreviewAction extends CAction {
	/**
	 * Run the action and perform the preview process
	 *
	 * @return void
	 * @since  XXX
	 */
	public function run($fileName) {
		Yii::trace(__CLASS__.'::'.__FUNCTION__.'()', 'Sweeml.actions');
		try {
			$tempFile = false;
			$sessionId = Yii::app()->getRequest()->getParam('key', Yii::app()->getSession()->getSessionId());
			$id = Yii::app()->getRequest()->getParam('id', 'unk');

			if (strncmp($fileName, 'tmp://', 6) === 0) {
				$tempFile = true;
				$fileName = str_replace('tmp://', '', $fileName);
				$targetPath = Yii::getPathOfAlias(SwUploadedFile::$targetPath).DIRECTORY_SEPARATOR.$sessionId.DIRECTORY_SEPARATOR.$id;
			} else {
				$targetPath = Yii::getPathOfAlias(Yii::app()->getRequest()->getParam('targetPathAlias', 'webroot'));
			}
			if($tempFile === false) {
				$nodeId = Yii::app()->getRequest()->getParam('nodeId', '');
				$contentId = Yii::app()->getRequest()->getParam('contentId', '');
				$tagId = Yii::app()->getRequest()->getParam('tagId', '');
				$groupId = Yii::app()->getRequest()->getParam('groupId', '');
				$targetPath = str_replace(array('{nodeId}', '{contentId}', '{tagId}', '{groupId}'), array($nodeId, $contentId, $tagId, $groupId), $targetPath);
			}
			$file = $targetPath.DIRECTORY_SEPARATOR.$fileName;
			$response = array('status' => false);
			if(is_file($file) === false) {
				$response['status'] = true;
				if($tempFile === true) {
					$relativeFile = 'tmp://'.$fileName;
				} else {
					$basePath = Yii::getPathOfAlias('webroot');
					$relativeFile = ltrim(str_replace($basePath, '', $file), '/');
				}
				$response['url'] = XHtml::normalizeUrl(array($this->id, 'mode' => 'raw', 'filename' =>$relativeFile));
				$response['path'] = $relativeFile;
				$response['name'] = $fileName;
			}

			if(Yii::app()->request->isAjaxRequest == true) {
				$this->getController()->renderJson($response);
			} else {
				echo CJSON::encode($response);
			}
		} catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'Sweeml.actions');
			throw $e;
		}
	}


	public function generateImage($fileName, $targetPathAlias='webroot') {
		Yii::trace(__CLASS__.'::'.__FUNCTION__.'()', 'Sweeml.actions');
		try {
			$tempFile = false;
			$sessionId = Yii::app()->getRequest()->getParam('key', Yii::app()->getSession()->getSessionId());
			$id = Yii::app()->getRequest()->getParam('id', 'unk');
			if (strncmp($fileName, 'tmp://', 6) === 0) {
				$tempFile = true;
				$fileName = str_replace('tmp://', '', $fileName);
				$targetPath = Yii::getPathOfAlias(SwUploadedFile::$targetPath).DIRECTORY_SEPARATOR.$sessionId.DIRECTORY_SEPARATOR.$id;
			} else {
				$targetPath = Yii::getPathOfAlias($targetPathAlias);
				$replacement = array(
						'__contentId' => Yii::app()->getRequest()->getParam('contentId', ''),
						'__nodeId__' => Yii::app()->getRequest()->getParam('nodeId', ''),
						'__groupId__' => Yii::app()->getRequest()->getParam('groupId', ''),
						'__tagId__' => Yii::app()->getRequest()->getParam('tagId', ''),
				);
				$targetPath = str_replace(array_keys($replacement), array_values($replacement), $targetPath);
			}
			$file = $targetPath.DIRECTORY_SEPARATOR.$fileName;
			if(is_file($file) === true) {
				if($tempFile === false) {
					$image = SwCacheImage::create($file)->resize(120,120)->setFit(true);
					$imageContentType = $image->getContentType();
					$imageData = file_get_contents($image->getUrl(true));
				} else {
					$image = SwImage::create($file)->resize(120,120)->setFit(true);
					$imageContentType = $image->getContentType();
					$imageData = $image->liveRender();
				}

			}
			header('Content-type: '.$imageContentType);
			header('Content-Disposition: inline; filename="'.$fileName.'";');
			echo $imageData;
		}
		catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'Sweeml.actions');
			throw $e;
		}
	}
}
<?php
use hubshop\models\ShopNode;
/**
 * SwHandleUploadedFile.php
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

Yii::import('ext.sweekit.web.SwUploadedFile');

 class SwUploadedFileBehavior extends CBehavior {

	/**
	 * Attach behavior to specific events
	 * (non-PHPdoc)
	 * @see CBehavior::events()
	 *
	 * @return array
	 * @since  XXX
	 */
	public function events() {
		return array(
			'onBeforeSave'=>'beforeSave',
			'onAfterDelete'=>'deleteFiles',
			'onAfterFind'=>'setOriginalValues', // populate values from the record
			'onAfterSave'=>'afterSave', // repopulate when everything was saved
		);
	}

	private $_shouldSave = false;

	public function beforeSave() {
		if ($this->getOwnerModel()->isNewRecord === false) {
			$this->saveFiles();
		} else {
			$this->_shouldSave = true;
		}
	}

	public function afterSave() {
		if ($this->_shouldSave === true) {
			$modelName = '';
			$pk = $this->getOwnerModel()->getPrimaryKey();
			$modelName = get_class($this->getOwnerModel());
			$modelToResave = $modelName::model()->findByPk($pk);
			$modelToResave->save();
			$this->getOwnerModel()->setAttributes($modelToResave->getAttributes(), false);
		}
		$this->setOriginalValues();
	}

	private $_model = null;

	public function setOwnerModel($model) {
		$this->_model = $model;
	}
	public function getOwnerModel() {
		if($this->_model === null) {
			$this->_model = $this->getOwner();
		}
		return $this->_model;
	}

 	/**
 	 * @var array parameters
 	 */
 	private $_pathParameters=array();

 	/**
 	 * Get raw parameters
 	 *
 	 * @return array
 	 * @since  XXX
 	 */
 	public function getPathParameters() {
 		return $this->_pathParameters;
 	}
 	/**
 	 * Prepare raw parameters
 	 *
 	 * @param array $pathParameters
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function setPathParameters($pathParameters) {
 		if(is_array($pathParameters) === true) {
 			$this->_pathParameters = $pathParameters;
 		}
 	}
 	/**
 	 * @var array expanded parameter
 	 */
 	private $_expandedPathParameters;
 	/**
 	 * convert raw path parameter to usable one (attributes name to real value)
 	 *
 	 * @return array
 	 * @since  XXX
 	 */
 	public function getExpandedPathParameters() {
 		if($this->_expandedPathParameters === null) {
 			$this->_expandedPathParameters = array();
 			foreach($this->getPathParameters() as $expandKey => $attribute) {
 				$this->_expandedPathParameters[$expandKey] = $this->getOwnerModel()->$attribute;
 			}
 		}
 		return $this->_expandedPathParameters;
 	}

 	/**
 	 * @var array
 	 */
	private $_attributesForFile=array();

	/**
	 * Define attributes which are handling files
	 *
	 * attributes should be configured using and array
	 * array(
 	 * 		'images' => array(
 	 * 			'asString' => true, // linearize using implode(', ') and preg_split()
 	 * 			'isMulti' => false, // default value
 	 * 			'targetPathAlias' => 'webroot', // default value
 	 * 			'targetUrl' => '', // default value
 	 * 		),
 	 * );
	 * @param array $attributesConfig
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setAttributesForFile($attributesConfig) {
		if(is_string($attributesConfig) === true) {
			$attributesConfig = array($attributesConfig);
		}
		foreach($attributesConfig as $key => $value) {
			if(is_string($value) === true) {
				$this->_attributesForFile[$value] = array(
					'asString' => true,
					'isMulti' => false,
					'targetPathAlias' => Yii::getPathOfAlias('webroot').DIRECTORY_SEPARATOR,
					'targetUrl' => ltrim(rtrim($value['targetUrl'],'/').'/','/'),
				);
			} elseif(is_array($value) === true) {
				$this->_attributesForFile[$key] = array(
					'asString' => (isset($value['asString']) === true)?CPropertyValue::ensureBoolean($value['asString']):true,
					'isMulti' => (isset($value['isMulti']) === true)?CPropertyValue::ensureBoolean($value['isMulti']):false,
					'targetPathAlias' => (Yii::getPathOfAlias((isset($value['targetPathAlias']) === true)?$value['targetPathAlias']:'webroot')).DIRECTORY_SEPARATOR,
					'targetUrl' => ltrim(((isset($value['targetUrl']) === true)?$value['targetUrl']:'').'/','/'),
				);
			}
		}
	}

	/**
	 * Get attributes configured as file handlers
	 *
	 * @return array
	 * @since  XXX
	 */
	public function getAttributesForFile() {
		return $this->_attributesForFile;
	}

	private $_originalValues=array();

	/**
	 * define original values to perform the difference
	 *
	 * @since  XXX
	 * @return void
	 */
	public function setOriginalValues() {
		foreach($this->getAttributesForFile() as $attribute => $config) {
			if($config['asString'] === true) {
				// we have files in string
				$this->_originalValues[$attribute] = preg_split('/[\s,]+/', $this->getOwnerModel()->$attribute, -1, PREG_SPLIT_NO_EMPTY);
			} else {
				if (($this->getOwnerModel()->$attribute === null) || empty($this->getOwnerModel()->$attribute)) {
					$this->_originalValues[$attribute] = array();
				} elseif (is_array($this->getOwnerModel()->$attribute) === true) {
						$this->_originalValues[$attribute] = $this->getOwnerModel()->$attribute;
				} else {
					$this->_originalValues[$attribute] = array($this->getOwnerModel()->$attribute);
				}
			}
		}
	}

	/**
	 * Get files originally defined
	 *
	 * @since  XXX
	 * @return array
	 */
	public function getOriginalValues() {
		return $this->_originalValues;
	}

	/**
	 * Save files and populate model attributes
	 *
	 * @since  XXX
	 * @return void
	 */
 	public function saveFiles() {
 		foreach($this->getAttributesForFile() as $attribute => $config) {
 			$targetPath = $config['targetPathAlias'];
			$targetUrl = $config['targetUrl'];

			// patch with parameters
			if(count($this->getExpandedPathParameters()) > 0) {
				$targetPath = str_replace(array_keys($this->getExpandedPathParameters()), array_values($this->getExpandedPathParameters()), $targetPath);
				$targetUrl = str_replace(array_keys($this->getExpandedPathParameters()), array_values($this->getExpandedPathParameters()), $targetUrl);
			}

			if(empty($targetPath) === false && is_dir($targetPath) === false) {
				mkdir($targetPath, 0755, true);
			}
			$uploadedFiles = SwUploadedFile::getInstances($this->getOwnerModel(), $attribute);

			if($config['asString'] === true) {
				$currentFiles = preg_split('/[\s,]+/', $this->getOwnerModel()->$attribute, -1, PREG_SPLIT_NO_EMPTY);
			} else {
				if ($config['isMulti'] === false) {
					if ($this->getOwnerModel()->$attribute === null) {
						$currentFiles = array();
					} elseif (is_array($this->getOwnerModel()->$attribute) === true) {
						$currentFiles = $this->getOwnerModel()->$attribute;
					} else {
						$currentFiles = array($this->getOwnerModel()->$attribute);
					}
				} else {
					$currentFiles = ($this->getOwnerModel()->$attribute !== null) ? $this->getOwnerModel()->$attribute : array();
				}
			}


			$indexFiles = array();


			foreach ($currentFiles as $file) {
				if(empty($file) === false) {
					if ( (strncmp('tmp://', $file, 6) != 0) && (in_array($file, $this->originalValues[$attribute]) === false)) {
						$file = $targetUrl.$file;
					}
					$indexFiles[$file] = $file;
				}
			}
			$newFiles = array();
			foreach ($uploadedFiles as $file) {
				$fileName = strtolower($file->getName());
				if (strncmp('tmp://', $file, 6) === 0) {
					$fileName = str_replace('tmp://', '', $fileName);
					$fileName = preg_replace(array('/[^_\-\.0-9a-z]/','/[_]+/'),array('_','_'), $fileName);
					$fileToSave = $targetPath.$fileName;
					$dbFile = $targetUrl.$fileName;

					if (file_exists($fileToSave) === true && is_file($fileToSave) === true) {
						$name = pathinfo($fileName);
						$fileName = $name['filename'].'-'.uniqid().'.'.$name['extension'];
						$fileToSave = $targetPath.$fileName;
						$dbFile = $targetUrl.$fileName;
					}
					if($file->saveAs($fileToSave)) {
						$newFiles[] = $dbFile;
						if(isset($indexFiles[$file->getName()]) === true) {
							$indexFiles[$file->getName()] = $dbFile;
						}
					}

				}
			}


			$filesToDelete = array_diff((($this->originalValues[$attribute] === null) ? array() : $this->originalValues[$attribute]), $indexFiles);
			foreach ($filesToDelete as $file) {
				//XXX: This line is used on creation of entity.
				// The files tmp:// is saved in attributes so we do not want to delete those files.
				// See afterSave
				if (strncmp($file, 'tmp://', 6) != 0) {
					$file = str_replace($targetUrl, $targetPath, $file);
					if (is_file($file) === true) {
						unlink($file);
					}
				}
			}
			if ($config['isMulti'] === false) {
				$finalFiles = array_values($indexFiles);
				$this->getOwnerModel()->$attribute = array_pop($finalFiles);
			} else {
				$this->getOwnerModel()->$attribute = ($config['asString'] === true)?implode(',', array_values($indexFiles)):array_values($indexFiles);
			}
 		}
 	}

 	/**
 	 * Delete files when they are not needed anymore
 	 *
 	 * @since  XXX
 	 * @return void
 	 */
 	public function deleteFiles() {
 		foreach($this->getOriginalValues() as $attribute => $filesToDelete) {
 			$config = $this->attributesForFile[$attribute];
 			$targetPath = $config['targetPathAlias'];
 			$targetUrl = $config['targetUrl'];

 			// patch with parameters
			if(count($this->getExpandedPathParameters()) > 0) {
				$targetPath = str_replace(array_keys($this->getPathParameters()), array_values($this->getPathParameters()), $targetPath);
				$targetUrl = str_replace(array_keys($this->getPathParameters()), array_values($this->getPathParameters()), $targetUrl);
			}
 			foreach($filesToDelete as $file) {
 				$file = str_replace($targetUrl, $targetPath, $file);
 				if(is_file($file) === true) {
 					unlink($file);
 				}
 			}
 		}
 	}
 }

<?php
/**
 * File SwUploadedFile.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   Sweeml.web
 * @since     1.1
 */

/**
 * Class SwUploadedFile
 * 
 * This component allow the user to retrieve files which where
 * uploaded using the plupload stuff
 * 
 * <code>
 * 	...
 * 		// file was created as Sweeml::asyncFileUpload($file, 'uploadedFile', $options)
 * 		$file = new MyFileModel();
 * 		if(isset($_POST['MyFileModel']) == true) {
 * 			// get instances : retrieve the file uploaded for current property
 * 			// we can retrieve the first uploaded file
 * 			$uplodadedFile = SwUploadedFile::getInstance($file, 'uploadedFile');
 * 			// $uploadedFile is an SwUploadFile
 * 			if($uploadedFile !== null) {
 * 				$uploadedFile->saveAs('targetDirectory/'.$uploadedFile->getName());
 * 			}
 * 		}
 * 	... 
 * </code>
 *
 * <code>
 * 	...
 * 		// file was created as multi file upload : Sweeml::asyncFileUpload($file, 'uploadedFile', array(..., multiSelection=>true,...)
 * 		$file = new MyFileModel();
 * 		if(isset($_POST['MyFileModel']) == true) {
 * 			// get instances : retrieve all files uploaded for current property
 * 			$uplodadedFiles = SwUploadedFile::getInstances($file, 'uploadedFile');
 * 			// $uplodadedFiles is an array
 * 			foreach($uplodadedFiles as $uploadedFile) {
 * 				$uploadedFile->saveAs('targetDirectory/'.$uploadedFile->getName());
 * 			}
 * 		}
 * 	... 
 * </code>
 * 
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  web
 * @package   Sweeml.web
 * @since     1.1
 */
class SwUploadedFile extends CComponent {
	public static $targetPath='application.runtime.sweeUpload';
	protected static $_targetPath;
	protected static $_files = null;
	
	protected $_name;
	protected $_tempName;
	protected $_extensionName;
	protected $_size;

	/**
	 * Define the path where files will be temporary saved
	 * 
	 * @return string
	 * @since  1.1.0
	 */
	public static function getTargetPath() {
		if(self::$_targetPath === null) {
			self::$_targetPath = Yii::getPathOfAlias(self::$targetPath);
			self::$_targetPath .= DIRECTORY_SEPARATOR.Yii::app()->getSession()->getSessionId();
		}
		return self::$_targetPath;
	}
	
	/**
	 * Returns an instance of the first uploaded file for selected attribute.
	 * The file should be uploaded using {@link Sweeml::asyncFileUpload}.
	 * 
	 * @param CModel $model     the model instance
	 * @param string $attribute the attribute name. Tabular file uploading is supported.
	 * 
	 * @return SwUploadedFile the instance of the uploaded file.
	 * @since  1.1.0
	 */
	public static function getInstance($model, $attribute) {
		$results = self::getInstances($model, $attribute);
		if(count($results)>0) {
			return $results[0];
		} else {
			return null;
		}
	}
	/**
	 * Returns an instance of the specified uploaded file.
	 * The name can be a plain string or a string like an array element (e.g. 'Post[imageFile]', or 'Post[0][imageFile]').
	 * @param string $name the name of the file input field.
	 * @return CUploadedFile the instance of the uploaded file.
	 * Null is returned if no file is uploaded for the specified name.
	 */
	public static function getInstanceByName($name) {
		$results = self::getInstancesByName($name);
		if(count($results)>0) {
			return $results[0];
		} else {
			return null;
		}
	}
	
	/**
	 * Returns all uploaded files for the given model attribute. Usefull for multi-upload
	 * 
	 * @param CModel $model     the model instance
	 * @param string $attribute the attribute name.
	 * 
	 * @return array array of SwUploadedFile objects.
	 * @since  1.1.0
	 */
	public static function getInstances($model, $attribute) {
		$infos = array();
		$infos['original'] = $attribute;
		Sweeml::resolveNameID($model, $attribute, $infos);
		$infos['class'] = get_class($model);
		$infos['attribute'] = $attribute;
		$infos['namelen'] = strlen($infos['name']);
		$files = array();
		if((isset($_POST[$infos['class']]) == true) && (isset(self::$_files[$infos['class']][$attribute]) == false)) {
			self::$_files[$infos['class']][$attribute] = array();
			self::searchData($infos, $_POST[$infos['class']]);
		}
		if(isset(self::$_files[$infos['class']][$attribute]) == true) {
			$files = self::$_files[$infos['class']][$infos['attribute']];
		}
		$results = array();
		foreach($files as $key => $value) {
			if(strncmp($key, $infos['name'], $infos['namelen']) === 0) {
				$results[] = $value;
			}
		}
		return $results;
	}

	/**
	 * Returns an array of instances for the specified array name.
	 *
	 * If multiple files were uploaded and saved as 'Files[0]', 'Files[1]',
	 * 'Files[n]'..., you can have them all by passing 'Files' as array name.
	 * @param string $name the name of the array of files
	 * @return array the array of CUploadedFile objects. Empty array is returned
	 * if no adequate upload was found. Please note that this array will contain
	 * all files from all subarrays regardless how deeply nested they are.
	 */
	public static function getInstancesByName($name) {
		$infos = array();
		$infos['original'] = $name;
		$infos['namelen'] = strlen($name);
		$files = array();
		if((isset($_POST[$name]) == true) && (isset(self::$_files[$name]) == false)) {
			self::$_files[$name] = array();
			self::searchDataByName($name, $_POST);
		}
		if(isset(self::$_files[$name]) === false) {
			$files = array();
		} else {
			$files = self::$_files[$name];
		}
		$results = array();
		if($files !== null) {
			foreach($files as $key => $value) {
				if(strncmp($key, $name, $infos['namelen']) === 0) {
					$results[] = $value;
				}
			}
		}
		return $results;
	}
	protected static function searchDataByName($name, $postData, $prevKey='') {
		$id = Sweeml::getIdByName($name);
		foreach($postData as $key => $value) {
			if($key === $name) {
				if(is_array($value) == true) {
					foreach($value as $idx => $data) {
						$myFile = self::getTargetPath().DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$data;
						if((file_exists($myFile)===true) && (is_file($myFile)==true)) {
							$fileInfo = pathinfo($myFile);
							self::$_files[$name][$name.'_'.$idx] = new SwUploadedFile($data, $myFile, $fileInfo['extension'], filesize($myFile));
						}
					}
				} else {
					// single upload
					$myFile = self::getTargetPath().DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$value;
					if((file_exists($myFile)===true) && (is_file($myFile)==true)) {
						$fileInfo = pathinfo($myFile);
						self::$_files[$name] = new SwUploadedFile($value, $myFile, $fileInfo['extension'], filesize($myFile));
					}
				}
			} elseif(is_array($value) == true) {
				self::searchData($name, $value, $prevKey.'['.$key.']');
			}
		}
	}
	/**
	 * Recursive method used to collect info data.
	 * The original method cannot be used anymore because $_FILES is not used.
	 * 
	 * 
	 * @param unknown_type $infos    model / attribute infos
	 * @param unknown_type $postData data to search in
	 * @param unknown_type $prevKey  concat keys to build correct name
	 */
	protected static function searchData($infos, $postData, $prevKey='') {
		foreach($postData as $key => $value) {
			if($key === $infos['attribute']) {
				if(is_array($value) == true) {
					// multi upload
					$testName = $infos['class'].$prevKey.'['.$infos['attribute'].']';
					$id = Sweeml::getIdByName($testName);
					
					foreach($value as $idx => $data) {
						$myFile = self::getTargetPath().DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$data;

						if((file_exists($myFile)===true) && (is_file($myFile)==true)) {
							$fileInfo = pathinfo($myFile);
						
							self::$_files[$infos['class']][$infos['attribute']][$testName.'_'.$idx] = new SwUploadedFile($data, $myFile, $fileInfo['extension'], filesize($myFile));
						}
					}
				} else {
					$testName = $infos['class'].$prevKey.'['.$infos['attribute'].']';
					$id = Sweeml::getIdByName($testName);
					// single upload
					$myFile = self::getTargetPath().DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$value;
					if((file_exists($myFile)===true) && (is_file($myFile)==true)) {
						$fileInfo = pathinfo($myFile);
						self::$_files[$infos['class']][$infos['attribute']][$testName] = new SwUploadedFile($value, $myFile, $fileInfo['extension'], filesize($myFile));
					}
				}
			} elseif(is_array($value) == true) {
				self::searchData($infos, $value, $prevKey.'['.$key.']');
			}
		}
	}
	
	/**
	 * Cleans up the loaded SwUploadedFile instances.
	 * This method is mainly used by test scripts to set up a fixture.
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public static function reset() {
		self::$_files=null;
	}

	/**
	 * Constructor.
	 * 
	 * Use {@link getInstance} to get an instance of an uploaded file.
	 * 
	 * @param string  $name      the original name of the file being uploaded
	 * @param string  $tempName  the path of the uploaded file on the server.
	 * @param string  $extension the extension of the uploaded file
	 * @param integer $size      the actual size of the uploaded file in bytes
	 * 
	 * @return SwUploadedFile
	 * @since  1.1.0
	 */
	public function __construct($name,$tempName,$extension,$size) {
		$this->_name=$name;
		$this->_tempName=$tempName;
		$this->_extensionName=$extension;
		$this->_size=$size;
	}

	/**
	 * String output.
	 * This is PHP magic method that returns string representation of an object.
	 * The implementation here returns the uploaded file's name.
	 * 
	 * @return string
	 * @since 1.1
	 */
	public function __toString() {
		return $this->_name;
	}

	/**
	 * Saves the uploaded file.
	 * 
	 * @param string  $file           the file path used to save the uploaded file
	 * @param boolean $deleteTempFile whether to delete the temporary file after saving.
	 * 
	 * @return boolean
	 * @since  1.1.0
	 */
	public function saveAs($file,$deleteTempFile=true)	{
		if($deleteTempFile) {
			$result = copy($this->_tempName, $file);
			unlink($this->_tempName);
			return $result;
		}
		else if(is_uploaded_file($this->_tempName))
			return copy($this->_tempName, $file);
		else
			return false;
	}

	/**
	 * Delete temporary file
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function delete() {
		if(file_exists($this->_tempName) == true) {
			unlink($this->_tempName);
		}
	}
	/**
	 * @return string the original name of the file being uploaded
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @return string the path of the uploaded file on the server.
	 * 
	 * Note: we need to create some kind of garbage collector
	 */
	public function getTempName() {
		return $this->_tempName;
	}

	/**
	 * @return integer the actual size of the uploaded file in bytes
	 */
	public function getSize() {
		return $this->_size;
	}

	/**
	 * @return string the file extension name for {@link name}.
	 * The extension name does not include the dot character. An empty string
	 * is returned if {@link name} does not have an extension name.
	 */
	public function getExtensionName() {
		return $this->_extensionName;
	}
}

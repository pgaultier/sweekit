<?php
/**
 * SwUploadCommand.php
 * 
 * PHP version 5.2+
 * 
 * Command file to cleanup xhr swf uploaded files
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  commands
 * @package   Sweeml.commands
 */	
Yii::import('ext.sweekit.web.SwUploadedFile');
/**
 * This command browse the xhr/swf upload file and remove 
 * old files 
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  commands
 * @package   Sweeml.commands
 */	
class SwUploadCommand extends CConsoleCommand {
	public $delay=10;
	private $_checkDate;
	/**
	 * Check files and remove old files
	 * @see CConsoleCommand::run()
	 * 
	 * @param $args mixed unused yet, only for compat purpose
	 * 
	 * @return void
	 * @since  1.1.0
	 */
    public function run($args) {
		try {
			$targetPath = $targetPath = Yii::getPathOfAlias(SwUploadedFile::$targetPath);
			$this->_checkDate = time() - ($this->delay*60);
    		$this->_checkDirectoriesRecursive($targetPath);
    	} catch(Exception $e) {
			Yii::log('Error in '.__CLASS__.'::'.__FUNCTION__.'():'.$e->getMessage(), CLogger::LEVEL_ERROR, 'Sweeml.commands');
			throw $e;
    	}
    }
    
    /**
     * Browse directories in order to clean up files
     * 
     * @param string $path temporary path
     * 
     * @return integer
     * @since  1.1.0
     */
    private function _checkDirectoriesRecursive($path) {
		$res = scandir($path);
		$nbFiles = 0;
		foreach($res as $newPath) {
			if(($newPath != '.') && ($newPath != '..')) {
				$newPath = $path.DIRECTORY_SEPARATOR.$newPath; 
				if(is_dir($newPath) == true) {
					$nbInsideFiles = $this->_checkDirectoriesRecursive($newPath);
					$nbFiles += $nbInsideFiles;
					if($nbInsideFiles == 0) {
						rmdir($newPath);
					}
				} elseif(is_file($newPath) == true) {
					$nbFiles++;
					$fileTime = filemtime($newPath);
					if($fileTime < $this->_checkDate) {
						if(unlink($newPath) == true) {
							$nbFiles--;
						}
					}
				}
			}
		}
		return $nbFiles;
	}
}
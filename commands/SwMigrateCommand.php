<?php
/**
 * SwMigrateCommand.php
 *
 * PHP version 5.2+
 *
 * Extend migrate command to allow multiples sources
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  commands
 * @package   Sweeml.commands
 */

Yii::import('system.cli.commands.MigrateCommand');

/**
 * This command find all migrations available and perform
 * migration process
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  commands
 * @package   Sweeml.commands
 * @since     XXX
 */
class SwMigrateCommand extends  MigrateCommand {
	/**
	 * @var mixed list of external migration path to use
	 */
	public $extendedMigrationPath;

	/**
	 * Transcode yii path to real path and check path exitences
	 * @see MigrateCommand::beforeAction()
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function beforeAction($action, $params) {
		if($this->extendedMigrationPath !== null) {
			if(is_string($this->extendedMigrationPath) === true) {
				$this->extendedMigrationPath = array($this->extendedMigrationPath);
			}
			for($i=0; $i<count($this->extendedMigrationPath); $i++) {
				$path=Yii::getPathOfAlias($this->extendedMigrationPath[$i]);
				if($path===false || !is_dir($path)) {
					echo 'Error: The migration directory does not exist: '.$this->extendedMigrationPath[$i]."\n";
					exit(1);
				}
				$this->extendedMigrationPath[$i] = $path;
			}
		}
		return parent::beforeAction($action, $params);
	}

	/**
	 * Find list of classic migration and the others which are
	 * inthe extended path
	 * @see MigrateCommand::getNewMigrations()
	 *
	 * @return array
	 * @since  XXX
	 */
	protected function getNewMigrations() {
		$applied=array();
		foreach($this->getMigrationHistory(-1) as $version=>$time)
			$applied[substr($version,1,13)]=true;

		$migrations=array();
		$handle=opendir($this->migrationPath);
		while(($file=readdir($handle))!==false) {
			if($file==='.' || $file==='..')
				continue;
			$path=$this->migrationPath.DIRECTORY_SEPARATOR.$file;
			if(preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/',$file,$matches) && is_file($path) && !isset($applied[$matches[2]]))
				$migrations[]=$matches[1];
		}
		closedir($handle);

		if(is_array($this->extendedMigrationPath) === true) {
			$extendedMigrations = array();
			foreach($this->extendedMigrationPath as $migrationPath) {
				$handle=opendir($migrationPath);
				while(($file=readdir($handle))!==false) {
					if($file==='.' || $file==='..')
						continue;
					$path=$migrationPath.DIRECTORY_SEPARATOR.$file;
					if(preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/',$file,$matches) && is_file($path) && !isset($applied[$matches[2]]))
						$migrations[]=$matches[1];
				}
				closedir($handle);
			}
		}
		sort($migrations);
		return $migrations;
	}

	/**
	 * Instantiate migration using default path if available.
	 * In other cases, we check if the migration can be found in the
	 * extended paths
	 * @see MigrateCommand::instantiateMigration()
	 *
	 * @return CDbMigration
	 * @since  XXX
	 */
	protected function instantiateMigration($class) {
		$file=$this->migrationPath.DIRECTORY_SEPARATOR.$class.'.php';
		if((is_file($file) === false) && (is_array($this->extendedMigrationPath) === true)) {
			foreach($this->extendedMigrationPath as $migrationPath) {
				$extendFile = $migrationPath.DIRECTORY_SEPARATOR.$class.'.php';
				if(is_file($extendFile) === true) {
					$file = $extendFile;
					break;
				}
			}
		}
		require_once($file);
		$migration=new $class;
		$migration->setDbConnection($this->getDbConnection());
		return $migration;
	}


}
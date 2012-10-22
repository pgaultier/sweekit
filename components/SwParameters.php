<?php
/**
 * SwParameters.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  components
 * @package   Sweeml.components
 */

/**
 * This application component allow the use of extended parameters
 * Check migration m121021_122255_createSwParameters.php to create
 * needed table
 *
 * Declare the component in config file
 * <code>
 * 'parameters' => array(
 * 		'class' => 'ext.sweekit.components.SwParameters',
 * ),
 * </code>
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  components
 * @package   Sweeml.components
 */
class SwParameters extends CApplicationComponent implements ArrayAccess {

	const CACHE_KEY='sweelix.sweekit.SwParameters';

	/**
	 * @var string id for database
	 *  component to use
	 */
	public $connectionID='db';

	/**
	 * @var string id for cache component to use
	 */
	public $cacheID='cache';

	/**
	 * @var string name of the table used for storing data
	 */
	public $tableName='{{parameters}}';

	/**
	 * @var array parameters
	 */
	private $_parameters;

	/**
	 * Init the module (preload data)
	 * @see CApplicationComponent::init()
	 *
	 * @return void
	 * @since  XXX
	 */
	public function init() {
		parent::init();
		$this->loadParameters();
		$this->_parameters = CMap::mergeArray($this->_parameters, Yii::app()->params);
	}

	/**
	 * Check if offset is available
	 * @see ArrayAccess::offsetExists()
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function offsetExists($offset){
		$result = $this->_parameters;
		$finalResult = true;
		if($offset !== null) {
			$keyPath = explode('.', $offset);
			foreach($keyPath as $element) {
				if($element === "") {
					$element = 0;
				}
				if(isset($result[$element])) {
					$result = $result[$element];
				} else {
					$finalResult = false;
					break;
				}
			}
		}
		return $finalResult;
	}

	/**
	 * Return values when data is linearized
	 * @see ArrayAccess::offsetGet()
	 *
	 * @return mixed
	 * @since  XXX
	 */
	public function offsetGet($offset) {
		$result = $this->_parameters;
		if($offset !== null) {
			$keyPath = explode('.', $offset);
			foreach($keyPath as $element) {
				if($element === "") {
					$element = 0;
				}
				if(isset($result[$element])) {
					$result = $result[$element];
				} else {
					trigger_error('Index \''.$element.'\' is undefined', E_USER_NOTICE);
					$result = false;
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Not implemented, parameters are readonly
	 * @see ArrayAccess::offsetSet()
	 *
	 * @return void
	 * @since  XXX
	 */
	public function offsetSet($offset, $value) {
		throw new CException('Parameters are read-only');
	}

	/**
	 * Not implemented, parameters are readonly
	 * @see ArrayAccess::offsetUnset()
	 *
	 * @return void
	 * @since  XXX
	 */
	public function offsetUnset($offset) {
		throw new CException('Parameters are read-only');
	}

	/**
	 * Fetch parameters from database and merge everything
	 * with classic params
	 *
	 * @return array
	 * @since  XXX
	 */
	protected function fetchParameters() {
		$dbConnection = Yii::app()->getComponent($this->connectionID);
		$data = array();
		if($dbConnection instanceof CDbConnection) {
			if($dbConnection->tablePrefix!==null)
				$table=preg_replace('/{{(.*?)}}/',$dbConnection->tablePrefix.'\1',$this->tableName);
			else
				$table=$this->tableName;
			if(in_array($table, $dbConnection->getSchema()->tableNames) === true) {
				$dataReader = $dbConnection->createCommand()->select(array('parameterId', 'parameterValue'))
				->from($this->tableName)
				->order('parameterId DESC')
				->query();
				foreach($dataReader as $row) {
					$data[$row['parameterId']] = $row['parameterValue'];
				}
			}
		}
		asort($data);
		$parameters = array();
		foreach($data as $key => $value) {
			$keyPath = explode('.', $key);
			$code = '$parameters[\''.implode('\'][\'',$keyPath).'\']';
			$checkCode = eval('return isset('.$code.');');
			if($checkCode === true) {
				$code = $code.'[]';
			}
			eval($code .' = $value;');
		}
		return $parameters;
	}

	/**
	 * Load parameters from cache if available
	 *
	 * @return void
	 * @since  XXX
	 */
	protected function loadParameters() {
		if($this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null) {
			if(($data=$cache->get(self::CACHE_KEY))!==false) {
				$this->_parameters = $data;
			} else {
				$this->_parameters = $this->fetchParameters();
				$cache->set(self::CACHE_KEY,$this->_parameters);
			}
		} else {
			$this->_parameters = $this->fetchParameters();
		}
	}
}

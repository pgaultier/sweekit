<?php
/**
 * SwVersioningBehavior.php
 *
 * PHP version 5.3+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweekit.behaviors
 * @since     XXX
 */

/**
 *
 * Add versioning support to active records
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweekit.behaviors
 * @since     XXX
 *
 * @property string  $tableName     versioning table name
 * @property string  $versionField  field used to store version
 * @property string  $dateField     field used to store date when record was created
 * @property string  $authorField   field used to store author of the version
 * @property string  $commentField  field used to store a comment
 * @property boolean $cascadeDelete define if versioned models should be deleted
 * @property string  $author        author of current version
 * @property string  $date          date of current version
 * @property string  $comment       comment of current version
 * @property integer $version       current version
 */
class SwVersioningBehavior extends CActiveRecordBehavior {

	/**
	 * @var string table name for version information
	 */
	private $_tableName;

	/**
	 * Define versioning table name
	 *
	 * @param string $tableName
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setTableName($tableName) {
		$this->_tableName = $tableName;
	}

	/**
	 * Get versioning table name
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getTableName() {
		if($this->_tableName === null) {
			$this->_tableName = preg_replace('/^(\{\{)?([^}]*)(\}\})?$/', '\1\2Version\3', $this->getOwner()->tableName());
		}
		return $this->_tableName;
	}

	/**
	 * @var string field used to store version number
	 */
	private $_versionField;

	/**
	 * Define version field
	 *
	 * @param string $field version field name
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setVersionField($field) {
		$this->_versionField = $field;
	}

	/**
	 * Get name of version field
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getVersionField() {
		if($this->_versionField === null) {
			$this->_versionField = 'versionNumber';
		}
		return $this->_versionField;
	}

	/**
	 * @var string field used to store version author
	 */
	private $_authorField;

	/**
	 * Define author field
	 *
	 * @param string $field author field name
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setAuthorField($field) {
		$this->_authorField = $field;
	}

	/**
	 * Get name of author field
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getAuthorField() {
		if($this->_authorField === null) {
			$this->_authorField = 'versionAuthor';
		}
		return $this->_authorField;
	}

	/**
	 * @var string field used to store version author
	 */
	private $_dateField;

	/**
	 * Define date field
	 *
	 * @param string $field date field name
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setDateField($field) {
		$this->_dateField = $field;
	}

	/**
	 * Get name of date field
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getDateField() {
		if($this->_dateField === null) {
			$this->_dateField = 'versionDate';
		}
		return $this->_dateField;
	}

	/**
	 * @var string field used to store version comment
	 */
	private $_commentField;

	/**
	 * Define comment field
	 *
	 * @param string $field comment field name
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setCommentField($field) {
		$this->_commentField = $field;
	}

	/**
	 * Get name of comment field
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getCommentField() {
		if($this->_commentField === null) {
			$this->_commentField = 'versionComment';
		}
		return $this->_commentField;
	}

	/**
	 * @var boolean should we delete versionned records when original record is deleted
	 */
	private $_cascadeDelete;

	/**
	 * Set if we have to delete versionned record when original
	 * record is deleted.
	 *
	 * @param boolean cascade mode
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setCascadeDelete($cascade) {
		$this->_cascadeDelete = $cascade;
	}

	/**
	 * Get if we have to delete versionned record when original
	 * record is deleted. Default to false
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function getCascadeDelete() {
		if($this->_cascadeDelete === null) {
			$this->_cascadeDelete = false;
		}
		return $this->_cascadeDelete;
	}

	/**
	 * @var array prepared primary condition
	 */
	private $_primaryKeyCondition;

	/**
	 * Prepare primaryKey condition to be usable
	 * in sql request
	 *
	 * @return array
	 * @since  XXX
	 */
	public function getPrimaryKeyCondition() {
		if($this->_primaryKeyCondition === null) {
			$table=$this->getOwner()->getMetaData()->tableSchema;
			$condition = array();
			$parameters = array();
			if(is_string($table->primaryKey)) {
				$param = ':'.$table->primaryKey;
				$condition[] = $table->primaryKey . ' = ' . $param;
				$parameters[$param] =  $this->getOwner()->{$table->primaryKey};
			} elseif(is_array($table->primaryKey)) {
				foreach($table->primaryKey as $name) {
					$param = ':'.$name;
					$condition[] = $name . ' = ' . $param;
					$parameters[$param] =  $this->getOwner()->$name;
				}
			}

			$condition = implode(' AND ', $condition);

			$this->_primaryKeyCondition = array($condition, $parameters);
		}
		return $this->_primaryKeyCondition;
	}

	/**
	 * Retrieve current version info from database
	 *
	 * @retunr void
	 * @since  XXX
	 */
	protected function prepareVersionInfo() {
		list($condition, $params) = $this->getPrimaryKeyCondition();
		$connection = $this->getOwner()->getDbConnection();
		$res = $connection->createCommand()->select(array($this->dateField, $this->authorField, $this->commentField, $this->versionField))
		->from($this->tableName)
		->where($condition, $params)
		->order($this->versionField.' DESC')
		->queryRow();

		$this->setVersion($res[$this->versionField]);
		$this->setComment($res[$this->commentField]);
		$this->setDate($res[$this->dateField]);
		$this->setVersion($res[$this->versionField]);
	}
	/**
	 * @var string author name
	 */
	private $_author;

	/**
	 * Define author name
	 *
	 * @param string $author author name
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setAuthor($author) {
		$this->_author = $author;
	}

	/**
	 * Get author name
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getAuthor() {
		if($this->_author === null) {
			$this->prepareVersionInfo();
		}
		return $this->_author;
	}

	/**
	 * @var string comment
	 */
	private $_comment;

	/**
	 * Define comment
	 *
	 * @param string $comment comment
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setComment($comment) {
		$this->_comment = $comment;
	}

	/**
	 * Get comment
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getComment() {
		if($this->_comment === null) {
			$this->prepareVersionInfo();
		}
		return $this->_comment;
	}

	/**
	 * @var string date of record creation
	 */
	private $_date;

	/**
	 * Define date of record creation
	 *
	 * @param string $date date of record creation
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setDate($date) {
		$this->_date = $date;
	}

	/**
	 * Get date of record creation
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getDate() {
		if($this->_date === null) {
			$this->prepareVersionInfo();
		}
		return $this->_date;
	}

	/**
	 * @var integer record version
	 */
	private $_version;

	/**
	 * Define record version
	 *
	 * @param integer $data date of record creation
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setVersion($version) {
		$this->_version = $version;
	}

	/**
	 * Get date of record version
	 *
	 * @return integer
	 * @since  XXX
	 */
	public function getVersion() {
		if($this->_version === null) {
			$this->prepareVersionInfo();
		}
		return $this->_version;
	}


	/**
	 * Perform version save while saving original active record
	 *
	 * @see CActiveRecordBehavior::afterSave()
	 *
	 * @return void
	 * @since  XXX
	 */
	public function afterSave($event) {
		$connection = $this->getOwner()->getDbConnection();

		list($condition, $params) = $this->getPrimaryKeyCondition();

		$versionNumber = $connection->createCommand()->select('COUNT(*)+1')
			->from($this->tableName)
			->where($condition, $params)->queryScalar();

		$originalAttributes = $this->getOwner()->getAttributes(false);
		$originalAttributes[$this->dateField] = new CDbExpression('NOW()');
		$originalAttributes[$this->authorField] = $this->getAuthor();
		$originalAttributes[$this->commentField] = $this->getComment();
		$originalAttributes[$this->versionField] = $versionNumber;

		$connection->createCommand()->insert($this->tableName, $originalAttributes);
	}

	/**
	 * Perform version remove while deleting original active record
	 *
	 * @see CActiveRecordBehavior::afterDelete()
	 *
	 * @return void
	 * @since  XXX
	 */
	public function afterDelete($event) {
		if($this->getCascadeDelete() === true) {
			list($condition, $params) = $this->getPrimaryKeyCondition();
			$connection = $this->getOwner()->getDbConnection();
			$connection->createCommand()->delete($this->getTableName(), $condition, $params);
			$this->setVersion(null);
			$this->setComment(null);
			$this->setDate(null);
			$this->setAuthor(null);
		}
	}
}

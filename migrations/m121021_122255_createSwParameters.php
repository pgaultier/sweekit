<?php
/**
 * m121021_122255_createSwParameters.php
 *
 * PHP version 5.3+
 *
 * Migration
 *
 * @author    Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright 2010-2012 Ibitux
 * @license   http://www.ibitux.com/license license
 * @version   XXX
 * @link      http://code.ibitux.net/projects/ibitux-gccds
 * @category  migrations
 * @package   Sweeml.migrations
 */
/**
 * This class create the Parameter table
 *
 * @author    Philippe Gaultier <pgaultier@ibitux.com>
 * @copyright 2010-2012 Ibitux
 * @license   http://www.ibitux.com/license license
 * @version   XXX
 * @link      http://code.ibitux.net/projects/ibitux-gccds
 * @category  components
 * @package   Sweeml.components
 */
class m121021_122255_createSwParameters extends CDbMigration {
	/**
	 * Apply current migration
	 *
	 * @return void
	 */
	public function safeUp() {
		$this->createTable(
			'{{parameters}}',
			array(
				'parameterId' => 'string NOT NULL COMMENT \'parameter key\'',
				'parameterType' => 'string NOT NULL DEFAULT \'string\'',
				'parameterValue' => 'text COMMENT \'parameter value\'',
				'parameterDateCreate' => 'datetime NOT NULL',
				'parameterDateUpdate' => 'datetime',
				'PRIMARY KEY(parameterId)',
			),
			'ENGINE=InnoDB DEFAULT CHARSET=utf8'
		);
	}
	/**
	 * Revert current migration
	 *
	 * @return void
	 */
	public function safeDown() {
		$this->dropTable('{{parameters}}');
	}
}
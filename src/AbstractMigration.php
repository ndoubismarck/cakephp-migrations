<?php
declare(strict_types=1);

/**
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Migrations;

use Cake\Utility\Hash;
use Phinx\Migration\AbstractMigration as BaseAbstractMigration;

class AbstractMigration extends BaseAbstractMigration
{
    /**
     * Whether the tables created in this migration
     * should auto-create an `id` field or not
     *
     * This option is global for all tables created in the migration file.
     * If you set it to false, you have to manually add the primary keys for your
     * tables using the Migrations\Table::addPrimaryKey() method
     *
     * @var bool
     */
    public $autoId = true;

    /**
     * Whether the tables should be backed up
     * before drop table function is called
     *
     * @var bool
     */
    public $autoBackup = true;

    /**
     * Returns an instance of the Table class.
     *
     * You can use this class to create and manipulate tables.
     *
     * @param string $tableName Table Name
     * @param array $options Options
     * @return \Migrations\Table
     */
    public function table($tableName, $options = [])
    {
        if ($this->autoId === false) {
            $options['id'] = false;
        }

        return new Table($tableName, $options, $this->getAdapter());
    }

    /**
     * @param string $tableName
     */
    public function backupTable($tableName){

    }

    /**
     * @param string $tableName
     */
    public function dropTable($tableName)
    {
        $this->backupTable($tableName);
        $this->table($tableName)->drop()->save();
    }

    /**
     * @param $tableName
     * @param array $columns
     */
    public function createOrUpdateTable(string $tableName, array $columns,$autoIncrementOffset = 0)
    {
        $table = $this->table($tableName, [
            'id' => false, 'primary_key' => ['id']
        ]);
        foreach ($columns as $column) {
            $name = Hash::get($column, 'name');
            $type = Hash::get($column, 'type');
            $options = Hash::get($column, 'options', []);
            if ($name === 'null') {
                continue;
            }
            if (!$table->exists()) {
                $table->addColumn($name, $type, $options);
            } else {
                if (!$table->hasColumn($name)) {
                    $table->addColumn($name, $type, $options);
                } else {
                    $table->changeColumn($name, $type, $options);
                }
            }
        }
        if ($table->exists()) {
            return $table->update();
        }
        $table->create();
        if($autoIncrementOffset > 0){
            $this->execute("ALTER TABLE $tableName AUTO_INCREMENT=$autoIncrementOffset");
        }
    }
}

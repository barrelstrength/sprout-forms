<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

class CreateFormContentTable extends Migration
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The table name
     */
    public $tableName;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'elementId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'title' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex($this->db->getIndexName($this->tableName, 'elementId,siteId'), $this->tableName, 'elementId,siteId', true);
        $this->addForeignKey($this->db->getForeignKeyName($this->tableName, 'elementId'), $this->tableName, 'elementId', '{{%elements}}', 'id', 'CASCADE');
        $this->addForeignKey($this->db->getForeignKeyName($this->tableName, 'siteId'), $this->tableName, 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');
    
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        return false;
    }
}

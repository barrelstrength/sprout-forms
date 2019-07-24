<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m190724_000000_add_conditional_logic_table migration.
 */
class m190724_000000_add_conditional_logic_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%sproutforms_conditional_logic}}', [
            'id' => $this->primaryKey(),
            'formId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'rule' => $this->text(),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_conditional_logic}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_conditional_logic}}',
            'formId'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_conditional_logic}}', 'formId'
            ),
            '{{%sproutforms_conditional_logic}}', 'formId',
            '{{%sproutforms_forms}}', 'id', 'CASCADE'
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190724_000000_add_conditional_logic_table cannot be reverted.\n";
        return false;
    }
}

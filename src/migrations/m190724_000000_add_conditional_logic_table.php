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
        $this->createTable('{{%sproutforms_conditionals}}', [
            'id' => $this->primaryKey(),
            'formId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'rules' => $this->text(),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_conditionals}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_conditionals}}',
            'formId'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_conditionals}}', 'formId'
            ),
            '{{%sproutforms_conditionals}}', 'formId',
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

<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m181120_000000_add_integrations_table migration.
 */
class m181120_000000_add_integrations_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%sproutforms_integrations}}', [
            'id' => $this->primaryKey(),
            'formId' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_integrations}}',
            'formId',
            false
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations}}', 'formId'
            ),
            '{{%sproutforms_integrations}}', 'formId',
            '{{%sproutforms_forms}}', 'id', 'CASCADE', null
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181120_000000_add_integrations_table cannot be reverted.\n";
        return false;
    }
}

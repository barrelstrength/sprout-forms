<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m190425_000000_add_integrations_entries migration.
 */
class m190425_000000_add_integrations_entries extends Migration
{
    /**
     * @return bool
     * @throws \Throwable
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%sproutforms_integrations_entries}}', [
            'id' => $this->primaryKey(),
            'entryId' => $this->integer(),
            'integrationId' => $this->integer()->notNull(),
            'isValid' => $this->boolean()->defaultValue(false),
            'message' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations_entries}}',
                'entryId',
                false, true
            ),
            '{{%sproutforms_integrations_entries}}',
            'entryId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations_entries}}',
                'integrationId',
                false, true
            ),
            '{{%sproutforms_integrations_entries}}',
            'integrationId'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations_entries}}', 'entryId'
            ),
            '{{%sproutforms_integrations_entries}}', 'entryId',
            '{{%sproutforms_entries}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations_entries}}', 'integrationId'
            ),
            '{{%sproutforms_integrations_entries}}', 'integrationId',
            '{{%sproutforms_integrations}}', 'id', 'CASCADE'
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190425_000000_add_integrations_entries cannot be reverted.\n";
        return false;
    }
}

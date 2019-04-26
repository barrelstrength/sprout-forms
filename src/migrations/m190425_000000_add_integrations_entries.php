<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\fields\formfields\OptIn;
use craft\db\Migration;
use barrelstrength\sproutbasereports\migrations\m190305_000002_update_record_to_element_types as BaseUpdateElements;
use craft\db\Query;

/**
 * m190425_000000_add_integrations_entries migration.
 */
class m190425_000000_add_integrations_entries extends Migration
{
    /**
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%sproutforms_integrations_entries}}', [
            'id' => $this->primaryKey(),
            'entryId' => $this->integer()->notNull(),
            'integrationId' => $this->integer()->notNull(),
            'message' => $this->text(),
            'details' => $this->text(),
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
            'entryId',
            false
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations_entries}}',
                'integrationId',
                false, true
            ),
            '{{%sproutforms_integrations_entries}}',
            'integrationId',
            false
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations_entries}}', 'entryId'
            ),
            '{{%sproutforms_integrations_entries}}', 'entryId',
            '{{%sproutforms_entries}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations_entries}}', 'integrationId'
            ),
            '{{%sproutforms_integrations_entries}}', 'integrationId',
            '{{%sproutforms_integrations}}', 'id', 'CASCADE', null
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

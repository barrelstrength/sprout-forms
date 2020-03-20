<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use Throwable;

/**
 * m190425_000000_add_integrations_entries migration.
 */
class m190425_000000_add_integrations_entries extends Migration
{
    /**
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%sproutforms_log}}', [
            'id' => $this->primaryKey(),
            'entryId' => $this->integer(),
            'integrationId' => $this->integer()->notNull(),
            'success' => $this->boolean()->defaultValue(false),
            'status' => $this->enum('status',
                [
                    'pending', 'notsent', 'completed'
                ])
                ->notNull()->defaultValue('pending'),
            'message' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_log}}',
                'entryId',
                false, true
            ),
            '{{%sproutforms_log}}',
            'entryId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_log}}',
                'integrationId',
                false, true
            ),
            '{{%sproutforms_log}}',
            'integrationId'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_log}}', 'entryId'
            ),
            '{{%sproutforms_log}}', 'entryId',
            '{{%sproutforms_entries}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_log}}', 'integrationId'
            ),
            '{{%sproutforms_log}}', 'integrationId',
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

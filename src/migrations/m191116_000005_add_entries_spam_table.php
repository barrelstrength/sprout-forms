<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m191116_000005_add_entries_spam_table migration.
 */
class m191116_000005_add_entries_spam_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->db->tableExists('{{%sproutforms_entries_spam_log}}')) {
            return true;
        }

        $this->createTable('{{%sproutforms_entries_spam_log}}', [
            'id' => $this->primaryKey(),
            'entryId' => $this->integer()->notNull(),
            'type' => $this->string(),
            'errors' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_entries_spam_log}}',
                'entryId',
                false, true
            ),
            '{{%sproutforms_entries_spam_log}}',
            'entryId'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_entries_spam_log}}', 'entryId'
            ),
            '{{%sproutforms_entries_spam_log}}', 'entryId',
            '{{%sproutforms_entries}}', 'id', 'CASCADE'
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000005_add_entries_spam_table cannot be reverted.\n";

        return false;
    }
}

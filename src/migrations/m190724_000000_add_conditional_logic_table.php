<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

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
        if ($this->db->tableExists('{{%sproutforms_rules}}')) {
            return true;
        }

        $this->createTable('{{%sproutforms_rules}}', [
            'id' => $this->primaryKey(),
            'formId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->defaultValue(false),
            'behaviorAction' => $this->string(),
            'behaviorTarget' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_rules}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_rules}}',
            'formId'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_rules}}', 'formId'
            ),
            '{{%sproutforms_rules}}', 'formId',
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

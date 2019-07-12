<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m190708_000000_add_confirmation_column migration.
 */
class m190708_000000_add_confirmation_column extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_integrations}}';

        if (!$this->db->columnExists($table, 'confirmation')) {
            $this->addColumn($table, 'confirmation', $this->string()->after('type'));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190708_000000_add_confirmation_column cannot be reverted.\n";
        return false;
    }
}

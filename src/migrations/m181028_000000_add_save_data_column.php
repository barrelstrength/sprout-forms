<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m181028_000000_add_save_data_column migration.
 */
class m181028_000000_add_save_data_column extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_forms}}';

        if (!$this->db->columnExists($table, 'saveData')) {
            $this->addColumn($table, 'saveData', $this->string()->after('submitButtonText'));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m181028_000000_add_save_data_column cannot be reverted.\n";
        return false;
    }
}

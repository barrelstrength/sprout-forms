<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

/**
 * m191116_000000_rename_integrations_log_table migration.
 */
class m191116_000000_rename_integrations_log_table extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $oldTable = '{{%sproutforms_log}}';
        $newTable = '{{%sproutforms_integrations_log}}';

        if ($this->db->tableExists($oldTable) && !$this->db->tableExists($newTable)){
            $this->renameTable($oldTable, $newTable);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191116_000000_rename_integrations_log_table cannot be reverted.\n";
        return false;
    }
}
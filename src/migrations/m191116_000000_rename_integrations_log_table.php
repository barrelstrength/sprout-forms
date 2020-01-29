<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m191116_000000_rename_integrations_log_table migration.
 */
class m191116_000000_rename_integrations_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $oldTable = '{{%sproutforms_log}}';
        $newTable = '{{%sproutforms_integrations_log}}';

        if ($this->db->tableExists($oldTable) && !$this->db->tableExists($newTable)) {
            $this->renameTable($oldTable, $newTable);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000000_rename_integrations_log_table cannot be reverted.\n";

        return false;
    }
}
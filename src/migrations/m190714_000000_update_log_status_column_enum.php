<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m190714_000000_update_log_status_column_enum migration.
 */
class m190714_000000_update_log_status_column_enum extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        if ($this->db->getIsPgsql()) {
            // Manually construct the SQL for Postgres
            $check = "[[status]] in ('pending', 'notsent', 'completed')";
            $this->execute("alter table {{%sproutforms_log}} drop constraint {{%sproutforms_log_status_check}}, add check ({$check})");
        } else {
            $this->alterColumn('{{%sproutforms_log}}', 'status', $this->enum('status', ['pending', 'notsent', 'completed'])->notNull()->defaultValue('pending'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190714_000000_update_log_status_column_enum cannot be reverted.\n";

        return false;
    }
}

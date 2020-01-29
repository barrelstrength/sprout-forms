<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m181101_000000_not_null_columns migration.
 */
class m181101_000000_not_null_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_forms}}';

        //for drop not null
        $this->alterColumn($table, 'groupId', $this->integer(11)->defaultValue(null));
        $this->alterColumn($table, 'displaySectionTitles', $this->tinyInteger(1)->defaultValue(false));
        $this->alterColumn($table, 'enableFileAttachments', $this->tinyInteger(1)->defaultValue(false));
        $this->alterColumn($table, 'saveData', $this->tinyInteger(1)->defaultValue(false));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m181101_000000_not_null_columns cannot be reverted.\n";

        return false;
    }
}

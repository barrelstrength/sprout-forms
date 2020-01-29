<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m191005_000001_rename_templateOverridesFolder_to_formTemplate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('{{%sproutforms_forms}}', 'templateOverridesFolder', 'formTemplate');
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191005_000001_rename_templateOverridesFolder_to_formTemplate cannot be reverted.\n";

        return false;
    }
}

<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasefields\migrations\m191218_000000_remove_addressHelper_from_settings;
use craft\db\Migration;

/**
 * m191218_000000_remove_addressHelper_from_settings_sproutforms migration.
 */
class m191218_000000_remove_addressHelper_from_settings_sproutforms extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $migration = new m191218_000000_remove_addressHelper_from_settings();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191218_000000_remove_addressHelper_from_settings_sproutforms cannot be reverted.\n";

        return false;
    }
}

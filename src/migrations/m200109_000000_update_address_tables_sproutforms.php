<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasefields\migrations\m200109_000000_update_address_tables;
use craft\db\Migration;

/**
 * m200109_000000_update_address_tables_sproutforms migration.
 */
class m200109_000000_update_address_tables_sproutforms extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $migration = new m200109_000000_update_address_tables();

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
        echo "m200109_000000_update_address_tables_sproutforms cannot be reverted.\n";

        return false;
    }
}

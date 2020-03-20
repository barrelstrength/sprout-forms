<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasefields\migrations\m190226_000000_add_address_table;
use craft\db\Migration;

/**
 * m190226_000000_add_address_table_sproutforms migration.
 */
class m190226_000000_add_address_table_sproutforms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m190226_000000_add_address_table();

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
        echo "m190226_000000_add_address_table_sproutforms cannot be reverted.\n";

        return false;
    }
}


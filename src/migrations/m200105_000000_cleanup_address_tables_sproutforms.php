<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasefields\migrations\m200105_000000_cleanup_address_tables;
use craft\db\Migration;

/**
 * m200105_000000_cleanup_address_tables_sproutforms migration.
 */
class m200105_000000_cleanup_address_tables_sproutforms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m200105_000000_cleanup_address_tables();

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
        echo "m200105_000000_cleanup_address_tables_sproutforms cannot be reverted.\n";
        return false;
    }
}

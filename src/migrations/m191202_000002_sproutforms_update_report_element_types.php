<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\m191202_000002_update_report_element_types;
use craft\db\Migration;

/**
 * m191202_000002_sproutforms_update_report_element_types migration.
 */
class m191202_000002_sproutforms_update_report_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m191202_000002_update_report_element_types();

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
        echo "m191202_000002_sproutforms_update_report_element_types cannot be reverted.\n";
        return false;
    }
}

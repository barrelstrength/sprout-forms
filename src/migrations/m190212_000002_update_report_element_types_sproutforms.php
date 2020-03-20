<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\m190212_000002_update_report_element_types;
use craft\db\Migration;

/**
 * m190212_000002_update_report_element_types_sproutforms migration.
 */
class m190212_000002_update_report_element_types_sproutforms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m190212_000002_update_report_element_types();

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
        echo "m190212_000002_update_report_element_types_sproutforms cannot be reverted.\n";

        return false;
    }
}

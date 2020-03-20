<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\m200110_000000_add_reports_emailColumn_column;
use craft\db\Migration;
use yii\base\NotSupportedException;

class m200110_000000_add_reports_emailColumn_column_sproutforms extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $migration = new m200110_000000_add_reports_emailColumn_column();

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
        echo "m200110_000000_add_reports_emailColumn_column_sproutforms cannot be reverted.\n";

        return false;
    }
}

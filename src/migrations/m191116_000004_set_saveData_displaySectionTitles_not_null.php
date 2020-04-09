<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\config\DbConfig;
use craft\db\Connection;
use craft\db\Migration;

/**
 * m191116_000004_set_saveData_displaySectionTitles_not_null migration.
 */
class m191116_000004_set_saveData_displaySectionTitles_not_null extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_forms}}';

        $this->update($table, [
            'displaySectionTitles' => false
        ], [
            'displaySectionTitles' => null
        ], [], false);

        $this->update($table, [
            'saveData' => true
        ], [
            'saveData' => null
        ], [], false);

        // https://github.com/yiisoft/yii2/issues/4492
        if (Craft::$app->getDb()->getDriverName() === Connection::DRIVER_PGSQL) {
            $this->alterColumn($table, 'displaySectionTitles', 'SET NOT NULL');
            $this->alterColumn($table, 'displaySectionTitles', 'SET DEFAULT FALSE');
            $this->alterColumn($table, 'saveData', 'SET NOT NULL');
            $this->alterColumn($table, 'saveData', 'SET DEFAULT TRUE');
        } else {
            $this->alterColumn($table, 'displaySectionTitles', $this->boolean()->defaultValue(false)->notNull());
            $this->alterColumn($table, 'saveData', $this->boolean()->defaultValue(true)->notNull());
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000004_set_saveData_displaySectionTitles_not_null cannot be reverted.\n";

        return false;
    }
}

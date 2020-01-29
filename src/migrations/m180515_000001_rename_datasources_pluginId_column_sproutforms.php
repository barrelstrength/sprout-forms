<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\m180515_000001_rename_datasources_pluginId_column;
use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m180515_000001_rename_datasources_pluginId_column_sproutforms migration.
 */
class m180515_000001_rename_datasources_pluginId_column_sproutforms extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $migration = new m180515_000001_rename_datasources_pluginId_column();

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
        echo "m180515_000001_rename_datasources_pluginId_column_sproutforms cannot be reverted.\n";

        return false;
    }
}

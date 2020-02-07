<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\m180417_000000_sproutreports_datasources_pluginid_column as SproutReportsPluginId;
use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m180417_000000_sproutforms_datasources_pluginid_column migration.
 */
class m180417_000000_sproutforms_datasources_pluginid_column extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $migration = new SproutReportsPluginId();

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
        echo "m180417_000000_sproutforms_datasources_pluginid_column cannot be reverted.\n";

        return false;
    }
}

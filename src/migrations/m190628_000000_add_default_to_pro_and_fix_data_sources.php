<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\m190628_000000_fix_data_sources;
use Craft;
use craft\db\Migration;
use craft\services\Plugins;

/**
 * m190628_000000_add_default_to_pro_and_fix_data_sources migration.
 */
class m190628_000000_add_default_to_pro_and_fix_data_sources extends Migration
{
    /**
     * @return bool
     * @throws \Throwable
     */
    public function safeUp(): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.edition', 'pro');

        $migration = new m190628_000000_fix_data_sources();

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
        echo "m190628_000000_add_default_to_pro_and_fix_data_sources cannot be reverted.\n";
        return false;
    }
}

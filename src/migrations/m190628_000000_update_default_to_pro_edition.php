<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Plugins;

/**
 * m190628_000000_update_default_to_pro_edition migration.
 */
class m190628_000000_update_default_to_pro_edition extends Migration
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

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190628_000000_update_default_to_pro_edition cannot be reverted.\n";
        return false;
    }
}

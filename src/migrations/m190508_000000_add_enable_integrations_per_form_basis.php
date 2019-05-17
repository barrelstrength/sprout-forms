<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Plugins;

/**
 * m190508_000000_add_enable_integrations_per_form_basis migration.
 */
class m190508_000000_add_enable_integrations_per_form_basis extends Migration
{
    /**
     * @return bool
     * @throws \Throwable
     */
    public function safeUp(): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $currentSettings = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');

        if (isset($currentSettings['enableSaveDataPerFormBasis'])) {
            $currentSettings['enableIntegrationsPerFormBasis'] = $currentSettings['enableSaveDataPerFormBasis'];
        }

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $currentSettings);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190508_000000_add_enable_integrations_per_form_basis cannot be reverted.\n";
        return false;
    }
}

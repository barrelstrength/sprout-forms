<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use Craft;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

/**
 * m191005_000000_update_form_settings migration.
 */
class m191005_000000_update_form_settings extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @return bool|void
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp()
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $schemaVersion = $projectConfig->get('plugins.'.$pluginHandle.'.schemaVersion', true);
        if (version_compare($schemaVersion, '3.5.0', '>=')) {
            return;
        }

        $pluginSettings = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');
        // Add renamed settings
        $enableSaveData = (int)$pluginSettings['enableSaveData'];
        $pluginSettings['enableSaveDataDefaultValue'] = $enableSaveData ?? 0;
        $pluginSettings['formTemplateDefaultValue'] = $pluginSettings['templateFolderOverride'] ?? '';

        // Remove deprecated settings
        unset(
            $pluginSettings['enableIntegrationsPerFormBasis'],
            $pluginSettings['enablePerFormTemplateFolderOverride'],
            $pluginSettings['enableSaveDataPerFormBasis'],
            $pluginSettings['templateFolderOverride'],
            $pluginSettings['enableSaveData']
        );

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $pluginSettings);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191005_000000_update_form_settings cannot be reverted.\n";

        return false;
    }
}

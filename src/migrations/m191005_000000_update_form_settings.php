<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\services\Plugins;
use ReflectionException;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

/**
 * m191005_000000_update_form_settings migration.
 */
class m191005_000000_update_form_settings extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool|void
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     * @throws ReflectionException
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

        $accessible = new AccessibleTemplates();
        $pluginSettings['formTemplateDefaultValue'] = empty($pluginSettings['templateFolderOverride']) ? $accessible->getTemplateId() : $pluginSettings['templateFolderOverride'];

        if ($enableSaveData && isset($pluginSettings['enableSaveDataPerFormBasis']) && !$pluginSettings['enableSaveDataPerFormBasis']) {
            // Let's set true to saveData on all forms
            $forms = (new Query())
                ->select(['id'])
                ->from(['{{%sproutforms_forms}}'])
                ->all();

            foreach ($forms as $form) {
                $this->update('{{%sproutforms_forms}}', ['saveData' => true], ['id' => $form['id']], [], false);
            }
        }

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

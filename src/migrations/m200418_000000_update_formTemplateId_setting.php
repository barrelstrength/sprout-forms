<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class m200418_000000_update_formTemplateId_setting extends Migration
{
    /**
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $schemaVersion = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.schemaVersion', true);
        if (version_compare($schemaVersion, '3.9.1', '>=')) {
            return true;
        }

        /** @noinspection ClassConstantCanBeUsedInspection */
        $settings = [
            0 => [
                'oldId' => 'sproutforms-accessibletemplates',
                'newId' => 'barrelstrength\sproutforms\formtemplates\AccessibleTemplates'
            ],
            1 => [
                'oldId' => 'sproutforms-basictemplates',
                'newId' => 'barrelstrength\sproutforms\formtemplates\BasicTemplates'
            ]
        ];

        $pluginSettings = Craft::$app->getProjectConfig()->get(Plugins::CONFIG_PLUGINS_KEY.'.sprout-forms.settings');

        $formTemplateId = $pluginSettings['formTemplateDefaultValue'] ?? 'barrelstrength\sproutforms\formtemplates\AccessibleTemplates';

        foreach ($settings as $setting) {
            if ($formTemplateId === $setting['oldId']) {
                $formTemplateId = $setting['newId'];
                break;
            }
        }

        Craft::$app->getProjectConfig()->set(Plugins::CONFIG_PLUGINS_KEY.'.sprout-forms.settings.formTemplateId', $formTemplateId, 'Added Sprout Forms `formTemplateId` setting.');

        Craft::$app->getProjectConfig()->remove(Plugins::CONFIG_PLUGINS_KEY.'.sprout-forms.settings.formTemplateDefaultValue', 'Removed Sprout Forms `formTemplateDefaultValue` setting');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200418_000000_update_formTemplateId_setting cannot be reverted.\n";

        return false;
    }
}

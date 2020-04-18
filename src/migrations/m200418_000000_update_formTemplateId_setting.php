<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use craft\db\Migration;
use craft\helpers\ProjectConfig;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\services\Plugins;
use Craft;
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

        $pluginSettings = Craft::$app->getProjectConfig()->get(Plugins::CONFIG_PLUGINS_KEY.'.sprout-forms.settings');

        $formTemplateId = $pluginSettings['formTemplateDefaultValue'] ?? 'barrelstrength\sproutforms\formtemplates\AccessibleTemplates';

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

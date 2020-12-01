<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\integrationtypes\CustomEndpoint;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class m190410_000000_add_payload_forwarding_to_integration extends Migration
{
    /**
     * @inheritDoc
     *
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        // Adds various schema updates so that calls to the
        // service layer in this migration don't throw errors.
        // - m200322_000000_add_form_method_and_message_columns
        // - m191116_000007_add_enable_captchas_form_setting
        // - m191005_000001_rename_templateOverridesFolder_to_formTemplate
        // - m200418_000001_rename_formTemplateId_column

        $table = '{{%sproutforms_forms}}';

        if (!$this->db->columnExists($table, 'submissionMethod')) {
            $this->addColumn($table, 'submissionMethod', $this->string()->defaultValue('sync')->notNull()->after('redirectUri'));
            $this->addColumn($table, 'errorDisplayMethod', $this->string()->defaultValue('inline')->notNull()->after('submissionMethod'));
            $this->addColumn($table, 'successMessage', $this->text()->after('errorDisplayMethod'));
            $this->addColumn($table, 'errorMessage', $this->text()->after('successMessage'));
        }

        if (!$this->db->columnExists($table, 'formTemplate')) {
            $this->renameColumn($table, 'templateOverridesFolder', 'formTemplate');
            $this->addColumn($table, 'enableCaptchas', $this->boolean()->after('formTemplate')->defaultValue(true)->notNull());
            $this->renameColumn($table, 'formTemplate', 'formTemplateId');
        }

        // End schema migration prep

        $forms = (new Query())
            ->select(['id', 'submitAction'])
            ->from(['{{%sproutforms_forms}}'])
            ->where('[[submitAction]] is not null')
            ->all();

        $type = 'barrelstrength\sproutforms\integrationtypes\CustomEndpoint';

        $enableIntegrations = false;

        foreach ($forms as $form) {
            $integrationRecord = new IntegrationRecord();
            $integrationRecord->type = $type;
            $integrationRecord->formId = $form['id'];
            $integrationRecord->enabled = true;

            /** @var CustomEndpoint $integration */
            $integration = new $type();
            $settings = [];

            if ($form['submitAction']) {
                $integration->formId = $form['id'];
                $enableIntegrations = true;
                $settings['submitAction'] = $form['submitAction'];
                $formFields = $integration->getSourceFormFieldsAsMappingOptions();
                $fieldMapping = [];
                foreach ($formFields as $formField) {
                    $fieldMapping[] = [
                        'sourceFormField' => $formField['value'],
                        'targetIntegrationField' => $formField['value']
                    ];
                }
                $settings['fieldMapping'] = $fieldMapping;

                $integrationRecord->name = $integration::displayName();
                $integrationRecord->settings = json_encode($settings);
                $integrationRecord->save();
            }
        }

        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';

        // Don't make the same config changes twice
        $schemaVersion = Craft::$app->projectConfig
            ->get('plugins.'.$pluginHandle.'.schemaVersion', true);

        if (version_compare($schemaVersion, '3.0.20', '<')) {
            // Add enableIntegrationsPerFormBasis setting
            $currentSettings = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');
            $currentSettings['enableIntegrationsPerFormBasis'] = $enableIntegrations;
            $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $currentSettings);
        }

        // Cleanup
        if ($this->db->columnExists('{{%sproutforms_forms}}', 'submitAction')) {
            $this->dropColumn('{{%sproutforms_forms}}', 'submitAction');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190410_000000_add_payload_forwarding_to_integration cannot be reverted.\n";

        return false;
    }
}

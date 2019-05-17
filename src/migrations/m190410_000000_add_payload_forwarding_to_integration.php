<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\integrationtypes\CustomEndpoint;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use craft\db\Migration;
use craft\db\Query;
use Craft;
use craft\services\Plugins;

/**
 * m190410_000000_add_payload_forwarding_to_integration migration.
 */
class m190410_000000_add_payload_forwarding_to_integration extends Migration
{
    /**
     * @inheritDoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $forms = (new Query())
            ->select(['id', 'submitAction'])
            ->from(['{{%sproutforms_forms}}'])
            ->where('[[submitAction]] is not null')
            ->all();

        $type = CustomEndpoint::class;

        $enableIntegrations = false;

        foreach ($forms as $form) {
            $integrationRecord = new IntegrationRecord();
            $integrationRecord->type = $type;
            $integrationRecord->formId = $form['id'];
            $integrationRecord->enabled = true;
            
            /** @var CustomEndpoint $integrationApi */
            $integrationApi = $integrationRecord->getIntegrationApi();
            $settings = [];

            if ($form['submitAction']) {
                $enableIntegrations = true;
                $settings['submitAction'] = $form['submitAction'];
                $formFields = $integrationApi->getFormFieldsAsMappingOptions();
                $fieldMapping = [];
                foreach ($formFields as $formField) {
                    $fieldMapping[] = [
                        'sproutFormField' => $formField['value'],
                        'integrationField' => $formField['value']
                    ];
                }
                $settings['fieldMapping'] = $fieldMapping;
                
                $integrationRecord->name = $integrationApi->getName();
                $integrationRecord->settings = json_encode($settings);
                $integrationRecord->save();
            }
        }

        // Add enableIntegrationsPerFormBasis setting
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $currentSettings = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');
        $currentSettings['enableIntegrationsPerFormBasis'] = $enableIntegrations;
        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $currentSettings);

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

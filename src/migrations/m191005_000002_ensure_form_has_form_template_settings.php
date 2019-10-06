<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\services\Plugins;
use Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m191005_000002_ensure_form_has_form_template_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $formTemplateDefaultValue = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.'sprout-forms.settings.formTemplateDefaultValue');

        // Default to our Default Form Template setting or fallback to the Accessible Template
        $this->update('{{%sproutforms_forms}}', [
            '[[formTemplate]]' => $formTemplateDefaultValue ?? 'sproutforms-accessibletemplates'
        ], '[[formTemplate]] is null', [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191005_000002_ensure_form_has_form_template_settings cannot be reverted.\n";

        return false;
    }
}

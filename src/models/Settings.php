<?php

namespace barrelstrength\sproutforms\models;

use barrelstrength\sproutbase\base\SproutSettingsInterface;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Model;
use Craft;

/**
 *
 * @property array $settingsNavItems
 */
class Settings extends Model implements SproutSettingsInterface
{
    public $pluginNameOverride = '';
    public $templateFolderOverride = '';
    public $enablePayloadForwarding = 0;
    public $enableSaveData = 1;
    public $enableSaveDataPerFormBasis = 0;
    public $saveDataByDefault = 1;
    public $enablePerFormTemplateFolderOverride = 0;
    public $captchaSettings = [];
    public $enableEditFormEntryViaFrontEnd = 0;

    /**
     * @inheritdoc
     */
    public function getSettingsNavItems(): array
    {
        // Added new items override if needed
        // 'fullPageForm' => true,
        // 'actionTemplate' => 'sprout/_includes/actionButton'
        // 'actionUrl' => 'sprout/settings/save-settings'
        $variables['entryStatuses'] = SproutForms::$app->entries->getAllEntryStatuses();

        return [
            'general' => [
                'label' => Craft::t('sprout-forms', 'General'),
                'url' => 'sprout-forms/settings/general',
                'selected' => 'general',
                'template' => 'sprout-forms/settings/general'
            ],
            'spam-protection' => [
                'label' => Craft::t('sprout-forms', 'Spam Protection'),
                'url' => 'sprout-forms/settings/spam-protection',
                'selected' => 'spam-protection',
                'template' => 'sprout-forms/settings/spamprotection'
            ],
            'entry-statuses' => [
                'label' => Craft::t('sprout-forms', 'Entry Statuses'),
                'url' => 'sprout-forms/settings/entry-statuses',
                'selected' => 'entry-statuses',
                'template' => 'sprout-forms/settings/entrystatuses',
                'actionTemplate' => 'sprout-forms/settings/entrystatuses/_actionStatusButton',
                'variables' => $variables
            ],
//            'bundles' => [
//                'label' => Craft::t('sprout-forms', 'Bundles'),
//                'url' => 'sprout-forms/settings/bundles',
//                'selected' => 'bundles',
//                'template' => 'sprout-base-import/bundles/bundle-cards',
//                'actionTemplate' => false,
//                'fullPageForm' => false,
//                'settingsForm' => false,
//                'variables' => $variables
//            ],
            'advanced' => [
                'label' => Craft::t('sprout-forms', 'Advanced'),
                'url' => 'sprout-forms/settings/advanced',
                'selected' => 'advanced',
                'template' => 'sprout-forms/settings/advanced'
            ]
        ];
    }

    public function rules(): array
    {
        return [
            [['templateFolderOverride'], 'required', 'on' => 'general']
        ];
    }
}

<?php

namespace barrelstrength\sproutforms\models;

use barrelstrength\sproutbase\base\SproutSettingsInterface;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Model;
use Craft;

/**
 *
 * @property array $settingsNavItems
 */
class Settings extends Model implements SproutSettingsInterface
{
    const SPAM_BEHAVIOR_SIMULATE = 'simulateSuccessful';
    const SPAM_BEHAVIOR_RELOAD = 'reloadPage';
    const SPAM_BEHAVIOR_DISPLAY_ERRORS = 'displaySpamErrors';

    public $pluginNameOverride = '';
    public $formTemplateDefaultValue = '';
    public $enableSaveData = 1;
    // simulateSuccessful|displaySpamErrors|reloadPage
    public $spamBehavior = self::SPAM_BEHAVIOR_SIMULATE;
    public $enableSaveDataDefaultValue = 1;
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

        $navItems = [
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
            ]
        ];

        $isPro = SproutBase::$app->settings->isEdition('sprout-forms', SproutForms::EDITION_PRO);

        if (!$isPro) {
            $navItems['upgrade'] = [
                'label' => Craft::t('sprout-forms', 'Upgrade to Pro'),
                'url' => 'sprout-forms/settings/upgrade',
                'selected' => 'upgrade',
                'template' => 'sprout-forms/settings/upgrade'
            ];
        }

        return $navItems;
    }

    public function rules(): array
    {
        return [
            [['formTemplateDefaultValue'], 'required', 'on' => 'general']
        ];
    }

    /**
     * @return array
     */
    public function getSpamBehaviorsAsOptions(): array
    {
        return [
            [
                'label' => 'Simulate successful submission',
                'value' => self::SPAM_BEHAVIOR_SIMULATE
            ],
            [
                'label' => 'Display error messages',
                'value' => self::SPAM_BEHAVIOR_DISPLAY_ERRORS
            ],
            [
                'label' => 'Reload Form',
                'value' => self::SPAM_BEHAVIOR_RELOAD
            ],
        ];
    }
}

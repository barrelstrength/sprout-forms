<?php

namespace barrelstrength\sproutforms\models;

use barrelstrength\sproutbase\base\SproutSettingsInterface;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Model;
use Craft;

/**
 *
 * @property array $spamRedirectBehaviorsAsOptions
 * @property array $settingsNavItems
 */
class Settings extends Model implements SproutSettingsInterface
{
    const SPAM_REDIRECT_BEHAVIOR_NORMAL = 'redirectAsNormal';
    const SPAM_REDIRECT_BEHAVIOR_BACK_TO_FORM = 'redirectBackToForm';

    public $pluginNameOverride = '';
    public $defaultSection = 'entries';
    public $formTemplateDefaultValue = '';
    public $enableSaveData = 1;
    public $spamRedirectBehavior = self::SPAM_REDIRECT_BEHAVIOR_NORMAL;
    public $saveSpamToDatabase = 0;
    public $spamLimit = 500;
    public $cleanupProbability = 1000;
    public $enableSaveDataDefaultValue = 1;
    public $trackRemoteIp = false;
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
        $spamProtectionVariables['spamRedirectBehaviorOptions'] = $this->getSpamRedirectBehaviorsAsOptions();
        $entryStatusVariables['entryStatuses'] = SproutForms::$app->entryStatuses->getAllEntryStatuses();
        $entryStatusVariables['spamStatusHandle'] = EntryStatus::SPAM_STATUS_HANDLE;

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
                'template' => 'sprout-forms/settings/spamprotection',
                'variables' => $spamProtectionVariables
            ],
            'entry-statuses' => [
                'label' => Craft::t('sprout-forms', 'Entry Statuses'),
                'url' => 'sprout-forms/settings/entry-statuses',
                'selected' => 'entry-statuses',
                'template' => 'sprout-forms/settings/entrystatuses',
                'actionTemplate' => 'sprout-forms/settings/entrystatuses/_actionStatusButton',
                'variables' => $entryStatusVariables
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
    public function getSpamRedirectBehaviorsAsOptions(): array
    {
        return [
            [
                'label' => 'Redirect as normal (recommended)',
                'value' => self::SPAM_REDIRECT_BEHAVIOR_NORMAL
            ],
            [
                'label' => 'Redirect back to form',
                'value' => self::SPAM_REDIRECT_BEHAVIOR_BACK_TO_FORM
            ]
        ];
    }
}

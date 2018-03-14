<?php

namespace barrelstrength\sproutforms;

use barrelstrength\sproutforms\integrations\sproutimport\elements\Form as FormElementImporter;
use barrelstrength\sproutforms\integrations\sproutimport\elements\Entry as EntryElementImporter;
use barrelstrength\sproutforms\integrations\sproutimport\fields\Forms as FormsFieldImporter;
use barrelstrength\sproutforms\integrations\sproutimport\fields\Entries as EntriesFieldImporter;
use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\services\sproutemail\NotificationEmails;
use barrelstrength\sproutbase\services\sproutreports\DataSources;
use barrelstrength\sproutbase\events\RegisterNotificationEvent;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\fields\Forms as FormsField;
use barrelstrength\sproutforms\fields\Entries as FormEntriesField;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\integrations\sproutforms\captchas\invisiblecaptcha\HoneypotCaptcha;
use barrelstrength\sproutforms\integrations\sproutforms\captchas\invisiblecaptcha\JavascriptCaptcha;
use barrelstrength\sproutforms\integrations\sproutforms\templates\SproutForms2;
use barrelstrength\sproutforms\integrations\sproutforms\templates\SproutForms3;
use barrelstrength\sproutforms\services\App;
use barrelstrength\sproutforms\services\Entries;
use barrelstrength\sproutforms\services\Forms;
use barrelstrength\sproutimport\services\Importers;
use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\services\UserPermissions;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\web\twig\variables\SproutFormsVariable;
use barrelstrength\sproutforms\events\RegisterFieldsEvent;
use barrelstrength\sproutforms\services\Fields as SproutFormsFields;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutforms\events\OnBeforePopulateEntryEvent;
use barrelstrength\sproutforms\controllers\EntriesController;
use barrelstrength\sproutforms\elements\Entry as EntryElement;

class SproutForms extends Plugin
{
    use BaseSproutTrait;

    /**
     * Enable use of SproutForms::$app-> in place of Craft::$app->
     *
     * @var App
     */
    public static $app;

    /**
     * Identify our plugin for BaseSproutTrait
     *
     * @var string
     */
    public static $pluginId = 'sprout-forms';

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    public function init()
    {
        parent::init();

        self::$app = $this->get('app');

        SproutBaseHelper::registerModule();

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(SproutFormsFields::class, SproutFormsFields::EVENT_REGISTER_FIELDS, function(RegisterFieldsEvent $event) {
            $fieldsByGroup = SproutForms::$app->fields->getRegisteredFieldsByGroup();

            foreach ($fieldsByGroup as $group) {
                foreach ($group as $field) {
                    $event->fields[] = new $field;
                }
            }
        });

        // Register DataSources for sproutReports plugin integration
        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = EntriesDataSource::class;
        });

        $this->setComponents([
            'sproutforms' => SproutFormsVariable::class
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('sproutForms', SproutFormsVariable::class);
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = FormsField::class;
            $event->types[] = FormEntriesField::class;
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Forms'] = $this->getUserPermissions();
        });

        Event::on(EntriesController::class, EntriesController::EVENT_BEFORE_POPULATE, function(OnBeforePopulateEntryEvent $event) {
            self::$app->entries->handleUnobfuscateEmailAddresses($event->form);
        });

        Event::on(NotificationEmails::class, NotificationEmails::EVENT_REGISTER_EMAIL_EVENTS, function(RegisterNotificationEvent $event) {
            //  @todo - improve email behavior
            #$formEvent =  new SaveEntryEvent();
            #$formEvent->setPluginId(static::$pluginId);

            #$event->availableEvents[] = $formEvent;
        });

        Event::on(Forms::class, Forms::EVENT_REGISTER_CAPTCHAS, function(Event $event) {
            $event->types[] = JavascriptCaptcha::class;
            $event->types[] = HoneypotCaptcha::class;
        });

        Event::on(Entries::class, EntryElement::EVENT_BEFORE_SAVE, function(OnBeforeSaveEntryEvent $event) {
            $captchas = SproutForms::$app->forms->getAllEnabledCaptchas();

            foreach ($captchas as $captcha) {
                $captcha->verifySubmission($event);
            }
        });

        Event::on(Forms::class, Forms::EVENT_REGISTER_GLOBAL_TEMPLATES, function(Event $event) {
            $event->types[] = SproutForms2::class;
            $event->types[] = SproutForms3::class;
        });

        Craft::$app->view->hook('sproutForms.modifyForm', function(&$context) {
            $captchas = SproutForms::$app->forms->getAllEnabledCaptchas();
            $captchaHtml = '';

            foreach ($captchas as $captcha) {
                $captchaHtml .= $captcha->getCaptchaHtml();
            }

            return $captchaHtml;
        });

        Event::on(Importers::class, Importers::EVENT_REGISTER_IMPORTER, function(RegisterComponentTypesEvent $event) {
            $event->types[] = FormElementImporter::class;
            $event->types[] = EntryElementImporter::class;
//            $event->types[] = FormsFieldImporter::class;
//            $event->types[] = EntriesFieldImporter::class;
        });
    }

    /**
     * @return Settings|\craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Redirect to Sprout Forms settings
     *
     * @return $this|mixed|\yii\web\Response
     */
    public function getSettingsResponse()
    {
        $url = UrlHelper::cpUrl('sprout-forms/settings');

        return Craft::$app->getResponse()->redirect($url);
    }

    /**
     * @return array|null
     */
    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();

        // Allow user to override plugin name in sidebar
        if ($this->getSettings()->pluginNameOverride) {
            $parent['label'] = $this->getSettings()->pluginNameOverride;
        }

        $entriesDataSource = SproutBase::$app->dataSources->getDataSourceByType(EntriesDataSource::class);

        return array_merge($parent, [
            'subnav' => [
                'forms' => [
                    'label' => Craft::t('sprout-forms', 'Forms'),
                    'url' => 'sprout-forms/forms'
                ],
                'entries' => [
                    'label' => Craft::t('sprout-forms', 'Entries'),
                    'url' => 'sprout-forms/entries'
                ],
                'notifications' =>[
                    'label' => Craft::t('sprout-forms', 'Notifications'),
                    'url' => 'sprout-forms/notifications'
                ],
                'reports' => [
                    'label' => Craft::t('sprout-forms', 'Reports'),
                    'url' => 'sprout-forms/reports/' . $entriesDataSource->dataSourceId . '-sproutforms-entriesdatasource'
                ],
                'settings' => [
                    'label' => Craft::t('sprout-forms', 'Settings'),
                    'url' => 'sprout-forms/settings'
                ]
            ]
        ]);
    }

    /**
     * @return array
     */
    private function getCpUrlRules()
    {
        return [
            'sprout-forms/forms/new' =>
                'sprout-forms/forms/edit-form-template',

            'sprout-forms/forms/edit/<formId:\d+>' =>
                'sprout-forms/forms/edit-form-template',

            'sprout-forms/entries/edit/<entryId:\d+>' =>
                'sprout-forms/entries/edit-entry',

            'sprout-forms/settings/(general|advanced)' =>
                'sprout-forms/settings/settings-index-template',

            'sprout-forms/settings/entry-statuses/new' =>
                'sprout-forms/entry-statuses/edit',

            'sprout-forms/settings/entry-statuses/<entryStatusId:\d+>' =>
                'sprout-forms/entry-statuses/edit',

            'sprout-forms/forms/<groupId:\d+>' =>
                'sprout-forms/forms',

            'sprout-forms/reports/<dataSourceId>-<dataSourceSlug>/new' => 'sprout-base/reports/edit-report',
            'sprout-forms/reports/<dataSourceId>-<dataSourceSlug>/edit/<reportId>' => 'sprout-base/reports/edit-report',
            'sprout-forms/reports/view/<reportId>' => 'sprout-base/reports/results-index',
            'sprout-forms/reports/<dataSourceId>-<dataSourceSlug>' => 'sprout-base/reports/index',

            'sprout-forms/notifications' => [
                'template' => 'sprout-base/sproutemail/notifications/index',
                'params' => [
                    'hideSidebar' => true
                ]
            ],
            'sprout-forms/settings/notifications/edit/<emailId:\d+|new>' => 'sprout-base/notifications/edit-notification-email-settings-template',
            'sprout-forms/notifications/edit/<emailId:\d+|new>' => 'sprout-base/notifications/edit-notification-email-template',

            'sprout-forms/settings' => 'sprout-base/settings/edit-settings',
            'sprout-forms/settings/<settingsSectionHandle:.*>' => 'sprout-base/settings/edit-settings'
        ];
    }

    /**
     * @return array
     */
    public function getUserPermissions()
    {
        return [
            'manageSproutFormsForms' => [
                'label' => Craft::t('sprout-forms','Manage Forms')
            ],
            'viewSproutFormsEntries' => [
                'label' => Craft::t('sprout-forms','View Form Entries'),
                'nested' => [
                    'editSproutFormsEntries' => [
                        'label' => Craft::t('sprout-forms','Edit Form Entries')
                    ]
                ]
            ],
            'editSproutFormsSettings' => [
                'label' => Craft::t('sprout-forms','Edit Settings')
            ]
        ];
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function afterInstall()
    {
        $dataSourceClasses = [
            EntriesDataSource::class
        ];

        SproutBase::$app->dataSources->installDataSources($dataSourceClasses);
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function beforeUninstall(): bool
    {
        $forms = SproutForms::$app->forms->getAllForms();

        foreach ($forms as $form) {
            SproutForms::$app->forms->deleteForm($form);
        }

        return true;
    }
}


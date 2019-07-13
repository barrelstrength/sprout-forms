<?php

namespace barrelstrength\sproutforms;

use barrelstrength\sproutbase\base\SproutEditionsInterface;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbaseemail\services\EmailTemplates;
use barrelstrength\sproutbaseemail\SproutBaseEmailHelper;
use barrelstrength\sproutbasefields\SproutBaseFieldsHelper;
use barrelstrength\sproutbaseemail\services\NotificationEmailEvents;
use barrelstrength\sproutbasereports\services\DataSources;
use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutbasereports\SproutBaseReportsHelper;
use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\integrations\sproutemail\emailtemplates\basic\BasicSproutFormsNotification;
use barrelstrength\sproutforms\integrations\sproutemail\emailtemplates\log\LogSproutFormsNotification;
use barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents\LogEvent;
use barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents\SaveEntryEvent;
use barrelstrength\sproutforms\integrations\sproutimport\elements\Form as FormElementImporter;
use barrelstrength\sproutforms\integrations\sproutimport\elements\Entry as EntryElementImporter;
use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbaseemail\events\NotificationEmailEvent;
use barrelstrength\sproutforms\fields\Forms as FormsField;
use barrelstrength\sproutforms\fields\Entries as FormEntriesField;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\SubmissionLogDataSource;
use barrelstrength\sproutforms\integrationtypes\EntryElementIntegration;
use barrelstrength\sproutforms\integrationtypes\CustomEndpoint;
use barrelstrength\sproutforms\services\Integrations;
use barrelstrength\sproutforms\widgets\RecentEntries;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\captchas\DuplicateCaptcha;
use barrelstrength\sproutforms\captchas\HoneypotCaptcha;
use barrelstrength\sproutforms\captchas\JavascriptCaptcha;
use barrelstrength\sproutforms\formtemplates\BasicTemplates;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutforms\services\App;
use barrelstrength\sproutforms\services\Entries;
use barrelstrength\sproutforms\services\Forms;
use barrelstrength\sproutbaseimport\services\Importers;
use Craft;
use craft\base\Plugin;

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\services\Plugins;
use craft\web\UrlManager;
use craft\services\UserPermissions;
use Exception;
use Throwable;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\web\twig\variables\SproutFormsVariable;
use barrelstrength\sproutforms\events\RegisterFieldsEvent;
use barrelstrength\sproutforms\services\Fields as SproutFormsFields;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use craft\services\Dashboard;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 *
 * @property null|array           $cpNavItem
 * @property array                $cpUrlRules
 * @property $this|Response|mixed $settingsResponse
 * @property null|string          $upgradeUrl
 * @property array                $userPermissions
 */
class SproutForms extends Plugin implements SproutEditionsInterface
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
    public static $pluginHandle = 'sprout-forms';

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var string
     */
    public $schemaVersion = '3.2.1';

    /**
     * @var string
     */
    public $minVersionRequired = '2.5.1';

    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Craft::setAlias('@sproutforms', $this->basePath);
        Craft::setAlias('@sproutformslib', dirname(__DIR__).'/lib');

        SproutBaseHelper::registerModule();
        SproutBaseEmailHelper::registerModule();
        SproutBaseFieldsHelper::registerModule();
        SproutBaseReportsHelper::registerModule();

        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = RecentEntries::class;
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Forms'] = $this->getUserPermissions();
        });

        Event::on(SproutFormsFields::class, SproutFormsFields::EVENT_REGISTER_FIELDS, static function(RegisterFieldsEvent $event) {
            $fieldsByGroup = SproutForms::$app->fields->getRegisteredFieldsByGroup();

            foreach ($fieldsByGroup as $group) {
                foreach ($group as $field) {
                    $event->fields[] = new $field;
                }
            }
        });

        // Register DataSources for sproutReports plugin integration
        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = EntriesDataSource::class;
            $event->types[] = SubmissionLogDataSource::class;
        });

        $this->setComponents([
            'sproutforms' => SproutFormsVariable::class
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            $event->sender->set('sproutForms', SproutFormsVariable::class);
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = FormsField::class;
            $event->types[] = FormEntriesField::class;
        });

        Event::on(NotificationEmailEvents::class, NotificationEmailEvents::EVENT_REGISTER_EMAIL_EVENT_TYPES, static function(NotificationEmailEvent $event) {
            $event->events[] = SaveEntryEvent::class;
            $event->events[] = LogEvent::class;
        });

        Event::on(Forms::class, Forms::EVENT_REGISTER_CAPTCHAS, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = DuplicateCaptcha::class;
            $event->types[] = JavascriptCaptcha::class;
            $event->types[] = HoneypotCaptcha::class;
        });

        Event::on(Entries::class, EntryElement::EVENT_BEFORE_SAVE, static function(OnBeforeSaveEntryEvent $event) {
            if (Craft::$app->getRequest()->getIsSiteRequest()) {
                /** @var Captcha[] $captchas */
                $captchas = SproutForms::$app->forms->getAllEnabledCaptchas();

                foreach ($captchas as $captcha) {
                    $captcha->verifySubmission($event);
                }
            }
        }, null, false);

        Event::on(Entries::class, EntryElement::EVENT_AFTER_SAVE, static function(OnSaveEntryEvent $event) {
            SproutForms::$app->integrations->runFormIntegrations($event->entry);
        });

        Craft::$app->view->hook('sproutForms.modifyForm', static function() {
            return SproutForms::$app->forms->getCaptchasHtml();
        });

        Event::on(Integrations::class, Integrations::EVENT_REGISTER_INTEGRATIONS, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = CustomEndpoint::class;
            $event->types[] = EntryElementIntegration::class;
        });

        Event::on(Forms::class, Forms::EVENT_REGISTER_FORM_TEMPLATES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = BasicTemplates::class;
            $event->types[] = AccessibleTemplates::class;
        });

        // Register Sprout Email Templates
        Event::on(EmailTemplates::class, EmailTemplates::EVENT_REGISTER_EMAIL_TEMPLATES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = BasicSproutFormsNotification::class;
            $event->types[] = LogSproutFormsNotification::class;
        });

        Event::on(Importers::class, Importers::EVENT_REGISTER_IMPORTER_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = FormElementImporter::class;
            $event->types[] = EntryElementImporter::class;
//            $event->types[] = FormsFieldImporter::class;
//            $event->types[] = EntriesFieldImporter::class;
        });

//        Event::on(Bundles::class, Bundles::EVENT_REGISTER_BUNDLE_TYPES, function(RegisterComponentTypesEvent $event) {
//            $event->types[] = BasicFieldsBundle::class;
//            $event->types[] = SpecialFieldsBundle::class;
//        });
    }

    /**
     * @inheritDoc
     */
    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getUpgradeUrl()
    {
        if (!SproutBase::$app->settings->isEdition('sprout-forms', self::EDITION_PRO)) {
            return UrlHelper::cpUrl('sprout-forms/upgrade');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Redirect to Sprout Forms settings
     *
     * @return $this|mixed|Response
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

        if (Craft::$app->getUser()->checkPermission('sproutForms-editForms')) {
            $parent['subnav']['forms'] = [
                'label' => Craft::t('sprout-forms', 'Forms'),
                'url' => 'sprout-forms/forms'
            ];
        }

        if (Craft::$app->getUser()->checkPermission('sproutForms-viewEntries')) {
            $parent['subnav']['entries'] = [
                'label' => Craft::t('sprout-forms', 'Entries'),
                'url' => 'sprout-forms/entries'
            ];
        }

        if (Craft::$app->getUser()->checkPermission('sproutForms-viewNotifications')) {
            $parent['subnav']['notifications'] = [
                'label' => Craft::t('sprout-forms', 'Notifications'),
                'url' => 'sprout-forms/notifications'
            ];
        }

        if (Craft::$app->getUser()->checkPermission('sproutForms-viewReports')) {
            $parent['subnav']['reports'] = [
                'label' => Craft::t('sprout-forms', 'Reports'),
                'url' => 'sprout-forms/reports'
            ];
        }

        if (Craft::$app->getUser()->getIsAdmin()) {
            $parent['subnav']['settings'] = [
                'label' => Craft::t('sprout-forms', 'Settings'),
                'url' => 'sprout-forms/settings'
            ];
        }

        return $parent;
    }

    /**
     * @return array
     */
    private function getCpUrlRules(): array
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

            // Reports
            '<pluginHandle:sprout-forms>/reports/<dataSourceId:\d+>/new' =>
                'sprout-base-reports/reports/edit-report-template',
            '<pluginHandle:sprout-forms>/reports/<dataSourceId:\d+>/edit/<reportId:\d+>' =>
                'sprout-base-reports/reports/edit-report-template',
            '<pluginHandle:sprout-forms>/reports/view/<reportId:\d+>' =>
                'sprout-base-reports/reports/results-index-template',
            '<pluginHandle:sprout-forms>/reports/<dataSourceId:\d+>' => [
                'route' => 'sprout-base-reports/reports/reports-index-template',
                'params' => [
                    'hideSidebar' => true
                ]
            ],
            '<pluginHandle:sprout-forms>/reports' => [
                'route' => 'sprout-base-reports/reports/reports-index-template',
                'params' => [
                    'hideSidebar' => true
                ]
            ],

            // Notifications
            '<pluginHandle:sprout-forms>/notifications' => [
                'route' => 'sprout-base-email/notifications/index',
                'params' => [
                    'hideSidebar' => true
                ]
            ],
            '<pluginHandle:sprout-forms>/notifications/edit/<emailId:\d+|new>' => [
                'route' => 'sprout-base-email/notifications/edit-notification-email-template',
                'params' => [
                    'defaultEmailTemplate' => BasicSproutFormsNotification::class
                ]
            ],
            '<pluginHandle:sprout-forms>/preview/<emailType:notification>/<emailId:\d+>' => [
                'route' => 'sprout-base-email/notifications/preview'
            ],
            '<pluginHandle:sprout-forms>/settings/notifications/edit/<emailId:\d+|new>' => [
                'route' => 'sprout-base-email/notifications/edit-notification-email-settings-template'
            ],

            // Settings
            'sprout-forms/settings' =>
                'sprout/settings/edit-settings',
            'sprout-forms/settings/<settingsSectionHandle:.*>' =>
                'sprout/settings/edit-settings'
        ];
    }

    /**
     * @return array
     */
    public function getUserPermissions(): array
    {
        return [
            'sproutForms-editForms' => [
                'label' => Craft::t('sprout-forms', 'Edit Forms')
            ],
            'sproutForms-viewEntries' => [
                'label' => Craft::t('sprout-forms', 'View Form Entries'),
                'nested' => [
                    'sproutForms-editEntries' => [
                        'label' => Craft::t('sprout-forms', 'Edit Form Entries')
                    ]
                ]
            ],

            // Notifications
            'sproutForms-viewNotifications' => [
                'label' => Craft::t('sprout-forms', 'View Notifications'),
                'nested' => [
                    'sproutForms-editNotifications' => [
                        'label' => Craft::t('sprout-forms', 'Edit Notification Emails')
                    ]
                ]
            ],

            // Reports
            'sproutForms-viewReports' => [
                'label' => Craft::t('sprout-forms', 'View Reports'),
                'nested' => [
                    'sproutForms-editReports' => [
                        'label' => Craft::t('sprout-forms', 'Edit Reports')
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function afterInstall()
    {
        // Add DataSource integrations so users don't have to install them manually
        $dataSourceTypes = [
            EntriesDataSource::class,
            SubmissionLogDataSource::class
        ];

        // @todo research why the plugin is not enabled after install
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.enabled', true);

        foreach ($dataSourceTypes as $dataSourceClass) {
            /** @var DataSource $dataSource */
            $dataSource = new $dataSourceClass();
            $dataSource->pluginHandle = 'sprout-forms';
            SproutBaseReports::$app->dataSources->saveDataSource($dataSource);
        }

        // Redirect to welcome page
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        Craft::$app->controller->redirect(UrlHelper::cpUrl('sprout-forms/welcome'))->send();
    }

    /**
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function beforeUninstall(): bool
    {
        $forms = self::$app->forms->getAllForms();

        foreach ($forms as $form) {
            self::$app->forms->deleteForm($form);
        }

        return true;
    }
}


<?php

namespace barrelstrength\sproutforms;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\services\sproutreports\DataSources;
use barrelstrength\sproutforms\services\App;
use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use craft\services\UserPermissions;
use yii\base\Event;
use craft\web\twig\variables\CraftVariable;

use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\web\twig\variables\SproutFormsVariable;
use barrelstrength\sproutforms\events\RegisterFieldsEvent;
use barrelstrength\sproutforms\integrations\sproutforms\fields\PlainText;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Number;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Dropdown;
use barrelstrength\sproutforms\integrations\sproutforms\fields\RadioButtons;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Checkboxes;
use barrelstrength\sproutforms\integrations\sproutforms\fields\MultiSelect;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Assets;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Categories;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Entries;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Tags;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Email;
use barrelstrength\sproutforms\integrations\sproutforms\fields\EmailSelect;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Hidden;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Invisible;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Link;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Notes;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Phone;
use barrelstrength\sproutforms\integrations\sproutforms\fields\RegularExpression;
use barrelstrength\sproutforms\services\Fields;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource;

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

    public $hasCpSection = true;
    public $hasCpSettings = true;

    public function init()
    {
        parent::init();

        self::$app = $this->get('app');
        SproutBaseHelper::registerModule();

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        }
        );

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELDS, function(RegisterFieldsEvent $event) {
            $event->fields[] = new PlainText();
            $event->fields[] = new Number();
            $event->fields[] = new Dropdown();
            $event->fields[] = new Checkboxes();
            $event->fields[] = new RadioButtons();
            $event->fields[] = new MultiSelect();
            $event->fields[] = new Assets();
            $event->fields[] = new Categories();
            $event->fields[] = new Entries();
            $event->fields[] = new Tags();
            $event->fields[] = new Email();
            $event->fields[] = new EmailSelect();
            $event->fields[] = new Hidden();
            $event->fields[] = new Invisible();
            $event->fields[] = new Link();
            $event->fields[] = new Phone();
            $event->fields[] = new RegularExpression();

            $redactor = Craft::$app->plugins->getPlugin('redactor');
            if ($redactor) {
                $event->fields[] = new Notes();
            }
        }
        );

        // Register DataSources for sproutReports plugin integration
        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = new EntriesDataSource();
        });

        $this->setComponents([
            'sproutforms' => SproutFormsVariable::class
        ]);

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                $variable = $event->sender;
                $variable->set('sproutforms', SproutFormsVariable::class);
            }
        );

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout Forms'] = $this->getUserPermissions();
        }
        );
    }

    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();

        // Allow user to override plugin name in sidebar
        if ($this->getSettings()->pluginNameOverride) {
            $parent['label'] = $this->getSettings()->pluginNameOverride;
        }

        return array_merge($parent, [
            'subnav' => [
                'entries' => [
                    'label' => SproutForms::t('Entries'),
                    'url' => 'sprout-forms/entries'
                ],
                'forms' => [
                    'label' => SproutForms::t('Forms'),
                    'url' => 'sprout-forms/forms'
                ],
                'reports' => [
                    'label' => SproutForms::t('Reports'),
                    'url' => 'sprout-forms/reports/sproutforms.entriesdatasource'
                ],
                'settings' => [
                    'label' => SproutForms::t('Settings'),
                    'url' => 'sprout-forms/settings'
                ]
            ]
        ]);
    }

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

            'sprout-forms/reports/<dataSourceId>/new' => 'sprout-base/reports/edit-report',
            'sprout-forms/reports/<dataSourceId>/edit/<reportId>' => 'sprout-base/reports/edit-report',
            'sprout-forms/reports/view/<reportId>' => 'sprout-base/reports/results-index',
            'sprout-forms/reports/<dataSourceId>' => 'sprout-base/reports/index',

            'sprout-forms/settings' => 'sprout-base/settings/edit-settings',
            'sprout-forms/settings/<settingsSectionHandle:.*>' => 'sprout-base/settings/edit-settings'
        ];
    }

    /**
     * @return []
     */
    public function getUserPermissions()
    {
        return [
            'manageSproutFormsForms' => [
                'label' => self::t('Manage Forms')
            ],
            'viewSproutFormsEntries' => [
                'label' => self::t('View Form Entries'),
                'nested' => [
                    'editSproutFormsEntries' => [
                        'label' => self::t('Edit Form Entries')
                    ]
                ]
            ],
            'editSproutFormsSettings' => [
                'label' => self::t('Edit Settings')
            ]
        ];
    }

    /**
     * @throws \Exception
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


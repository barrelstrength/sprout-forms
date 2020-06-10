<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms;

use barrelstrength\sproutbase\app\email\events\NotificationEmailEvent;
use barrelstrength\sproutbase\app\email\services\EmailTemplates;
use barrelstrength\sproutbase\app\email\services\NotificationEmailEvents;
use barrelstrength\sproutbase\app\forms\base\Captcha;
use barrelstrength\sproutbase\app\forms\captchas\DuplicateCaptcha;
use barrelstrength\sproutbase\app\forms\captchas\HoneypotCaptcha;
use barrelstrength\sproutbase\app\forms\captchas\JavascriptCaptcha;
use barrelstrength\sproutbase\app\forms\controllers\EntriesController;
use barrelstrength\sproutbase\app\forms\elements\Entry as EntryElement;
use barrelstrength\sproutbase\app\forms\events\OnBeforeValidateEntryEvent;
use barrelstrength\sproutbase\app\forms\events\OnSaveEntryEvent;
use barrelstrength\sproutbase\app\forms\events\RegisterFieldsEvent;
use barrelstrength\sproutbase\app\forms\fields\Entries as FormEntriesField;
use barrelstrength\sproutbase\app\forms\fields\Forms as FormsField;
use barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutbase\app\forms\formtemplates\BasicTemplates;
use barrelstrength\sproutbase\app\forms\integrations\sproutemail\emailtemplates\basic\BasicSproutFormsNotification;
use barrelstrength\sproutbase\app\forms\integrations\sproutemail\events\notificationevents\SaveEntryEvent;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\IntegrationLogDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\SpamLogDataSource;
use barrelstrength\sproutbase\app\forms\integrationtypes\CustomEndpoint;
use barrelstrength\sproutbase\app\forms\integrationtypes\EntryElementIntegration;
use barrelstrength\sproutbase\app\forms\services\Entries;
use barrelstrength\sproutbase\app\forms\services\Fields as SproutFormsFields;
use barrelstrength\sproutbase\app\forms\services\Forms;
use barrelstrength\sproutbase\app\forms\services\Integrations;
use barrelstrength\sproutbase\app\forms\widgets\RecentEntries;
use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\config\base\EditionsInterface;
use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\config\configs\EmailConfig;
use barrelstrength\sproutbase\config\configs\FieldsConfig;
use barrelstrength\sproutbase\config\configs\FormsConfig;
use barrelstrength\sproutbase\config\configs\ControlPanelConfig;
use barrelstrength\sproutbase\config\configs\ReportsConfig;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Fields;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use Exception;
use Throwable;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class SproutForms extends SproutBasePlugin implements EditionsInterface
{
    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';

    /**
     * @var string
     */
    public $schemaVersion = '3.11.8';

    /**
     * @var string
     */
    public $minVersionRequired = '3.12.2';

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

    public static function getSproutConfigs(): array
    {
        return [
            ControlPanelConfig::class,
            EmailConfig::class,
            FieldsConfig::class,
            FormsConfig::class,
            ReportsConfig::class
        ];
    }

    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();

        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = RecentEntries::class;
        });

        Event::on(SproutFormsFields::class, SproutFormsFields::EVENT_REGISTER_FIELDS, static function(RegisterFieldsEvent $event) {
            $fieldsByGroup = SproutBase::$app->fields->getRegisteredFieldsByGroup();

            foreach ($fieldsByGroup as $group) {
                foreach ($group as $field) {
                    $event->fields[] = new $field;
                }
            }
        });

        // Register DataSources for sproutReports plugin integration
        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = EntriesDataSource::class;
            $event->types[] = IntegrationLogDataSource::class;
            $event->types[] = SpamLogDataSource::class;
        });

//        $this->setComponents([
//            'sproutforms' => SproutFormsVariable::class
//        ]);

//        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
//            $event->sender->set('sproutForms', SproutFormsVariable::class);
//        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = FormsField::class;
            $event->types[] = FormEntriesField::class;
        });

        Event::on(NotificationEmailEvents::class, NotificationEmailEvents::EVENT_REGISTER_EMAIL_EVENT_TYPES, static function(NotificationEmailEvent $event) {
            $event->events[] = SaveEntryEvent::class;
        });

        Event::on(Forms::class, Forms::EVENT_REGISTER_CAPTCHAS, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = DuplicateCaptcha::class;
            $event->types[] = JavascriptCaptcha::class;
            $event->types[] = HoneypotCaptcha::class;
        });

        Event::on(EntriesController::class, EntriesController::EVENT_BEFORE_VALIDATE, static function(OnBeforeValidateEntryEvent $event) {

            if (Craft::$app->getRequest()->getIsSiteRequest()) {
                $enableCaptchas = (int)$event->form->enableCaptchas;

                // Don't process captchas if the form is set to ignore them
                if (!$enableCaptchas) {
                    return;
                }

                /** @var Captcha[] $captchas */
                $captchas = SproutBase::$app->forms->getAllEnabledCaptchas();

                foreach ($captchas as $captcha) {
                    $captcha->verifySubmission($event);
                    $event->entry->addCaptcha($captcha);
                }
            }
        }, null, false);

        Event::on(Entries::class, EntryElement::EVENT_AFTER_SAVE, static function(OnSaveEntryEvent $event) {
            SproutBase::$app->integrations->runFormIntegrations($event->entry);
        });

        Craft::$app->view->hook('sproutForms.modifyForm', static function(array &$context) {
            return SproutBase::$app->forms->handleModifyFormHook($context);
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
        });
    }

    /**
     * @inheritDoc
     */
    public function getUpgradeUrl()
    {
        if (!SproutBase::$app->config->isEdition('sprout-forms', self::EDITION_PRO)) {
            return UrlHelper::cpUrl('sprout-forms/upgrade');
        }

        return null;
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

    /**
     * @throws ErrorException
     * @throws \yii\base\Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    protected function afterInstall()
    {
        // Add DataSource integrations so users don't have to install them manually
        $dataSourceTypes = [
            EntriesDataSource::class,
            IntegrationLogDataSource::class,
            SpamLogDataSource::class
        ];

        // @todo research why the plugin is not enabled after install
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.enabled', true);

        foreach ($dataSourceTypes as $dataSourceClass) {
            /** @var DataSource $dataSource */
            $dataSource = new $dataSourceClass();
            SproutBase::$app->dataSources->saveDataSource($dataSource);
        }

        // Redirect to welcome page
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        Craft::$app->controller->redirect(UrlHelper::cpUrl('sprout-forms/welcome'))->send();
    }
}


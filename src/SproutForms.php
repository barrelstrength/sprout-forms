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
use barrelstrength\sproutbase\app\forms\controllers\FormEntriesController;
use barrelstrength\sproutbase\app\forms\elements\Entry as EntryElement;
use barrelstrength\sproutbase\app\forms\events\OnBeforeValidateEntryEvent;
use barrelstrength\sproutbase\app\forms\events\OnSaveEntryEvent;
use barrelstrength\sproutbase\app\forms\events\RegisterFieldsEvent;
use barrelstrength\sproutbase\app\forms\fields\Entries as FormEntriesField;
use barrelstrength\sproutbase\app\forms\fields\Forms as FormsField;
use barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutbase\app\forms\integrations\sproutemail\emailtemplates\basic\BasicSproutFormsNotification;
use barrelstrength\sproutbase\app\forms\integrations\sproutemail\events\notificationevents\SaveEntryEvent;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\IntegrationLogDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\SpamLogDataSource;
use barrelstrength\sproutbase\app\forms\integrationtypes\CustomEndpoint;
use barrelstrength\sproutbase\app\forms\integrationtypes\EntryElementIntegration;
use barrelstrength\sproutbase\app\forms\services\FormCaptchas;
use barrelstrength\sproutbase\app\forms\services\FormEntries;
use barrelstrength\sproutbase\app\forms\services\FormFields as SproutFormsFields;
use barrelstrength\sproutbase\app\forms\services\Forms;
use barrelstrength\sproutbase\app\forms\services\FormIntegrations;
use barrelstrength\sproutbase\app\forms\services\FormTemplates;
use barrelstrength\sproutbase\app\forms\widgets\RecentEntries;
use barrelstrength\sproutbase\app\reports\services\DataSources;
use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\config\configs\ControlPanelConfig;
use barrelstrength\sproutbase\config\configs\EmailPreviewConfig;
use barrelstrength\sproutbase\config\configs\FieldsConfig;
use barrelstrength\sproutbase\config\configs\FormsConfig;
use barrelstrength\sproutbase\config\configs\NotificationsConfig;
use barrelstrength\sproutbase\config\configs\ReportsConfig;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Fields;
use Exception;
use Throwable;
use yii\base\Event;

class SproutForms extends SproutBasePlugin
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
            NotificationsConfig::class,
            EmailPreviewConfig::class,
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

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = FormsField::class;
            $event->types[] = FormEntriesField::class;
        });

        Event::on(NotificationEmailEvents::class, NotificationEmailEvents::EVENT_REGISTER_EMAIL_EVENT_TYPES, static function(NotificationEmailEvent $event) {
            $event->events[] = SaveEntryEvent::class;
        });

        Event::on(FormCaptchas::class, FormCaptchas::EVENT_REGISTER_CAPTCHAS, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = DuplicateCaptcha::class;
            $event->types[] = JavascriptCaptcha::class;
            $event->types[] = HoneypotCaptcha::class;
        });

        Event::on(FormIntegrations::class, FormIntegrations::EVENT_REGISTER_INTEGRATIONS, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = CustomEndpoint::class;
            $event->types[] = EntryElementIntegration::class;
        });

        Event::on(FormTemplates::class, FormTemplates::EVENT_REGISTER_FORM_TEMPLATES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = AccessibleTemplates::class;
        });

        Event::on(EmailTemplates::class, EmailTemplates::EVENT_REGISTER_EMAIL_TEMPLATES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = BasicSproutFormsNotification::class;
        });

        Event::on(
            SproutFormsFields::class,
            SproutFormsFields::EVENT_REGISTER_FIELDS, [
            SproutBase::$app->formFields, 'handleRegisterFormFieldsEvent'
        ]);

        Event::on(
            FormEntriesController::class,
            FormEntriesController::EVENT_BEFORE_VALIDATE, [
            SproutBase::$app->formCaptchas, 'handleFormCaptchasEvent'
        ], null, false);

        Event::on(
            FormEntries::class,
            EntryElement::EVENT_AFTER_SAVE, [
            SproutBase::$app->formIntegrations, 'handleFormIntegrations'
        ]);

        Craft::$app->view->hook('sproutForms.modifyForm', static function(array &$context) {
            return SproutBase::$app->forms->handleModifyFormHook($context);
        });

    }

    /**
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function beforeUninstall(): bool
    {
        $forms = SproutBase::$app->forms->getAllForms();

        foreach ($forms as $form) {
            SproutBase::$app->forms->deleteForm($form);
        }

        return true;
    }

    protected function afterInstall()
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        // Redirect to welcome page
        $url = UrlHelper::cpUrl('sprout/welcome/forms');
        Craft::$app->controller->redirect($url)->send();
    }
}


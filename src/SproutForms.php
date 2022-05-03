<?php

namespace BarrelStrength\SproutForms;

use BarrelStrength\Sprout\core\db\InstallHelper;
use BarrelStrength\Sprout\core\db\SproutPluginMigrationInterface;
use BarrelStrength\Sprout\core\db\SproutPluginMigrator;
use BarrelStrength\Sprout\core\editions\Edition;
use BarrelStrength\Sprout\core\modules\Modules;
use BarrelStrength\Sprout\fields\FieldsModule;
use BarrelStrength\Sprout\forms\FormsModule;
use BarrelStrength\Sprout\reports\ReportsModule;
use Craft;
use craft\base\Plugin;
use craft\db\MigrationManager;
use craft\errors\MigrationException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use yii\base\Event;
use yii\base\InvalidConfigException;

class SproutForms extends Plugin implements SproutPluginMigrationInterface
{
    public const EDITION_LITE = 'lite';
    public const EDITION_PRO = 'pro';

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

    public static function getSchemaDependencies(): array
    {
        return [
            FormsModule::class,
            FieldsModule::class,
            ReportsModule::class,
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMigrator(): MigrationManager
    {
        return SproutPluginMigrator::make($this);
    }

    public string $schemaVersion = '0.0.1.3';

    public function init()
    {
        parent::init();
        
        Event::on(
            Modules::class,
            Modules::EVENT_REGISTER_SPROUT_AVAILABLE_MODULES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = FormsModule::class;
                $event->types[] = ReportsModule::class;
            }
        );

        $this->instantiateSproutModules();
        $this->grantModuleEditions();
    }

    protected function instantiateSproutModules(): void
    {
        FormsModule::isEnabled() && FormsModule::getInstance();
        ReportsModule::isEnabled() && ReportsModule::getInstance();
    }

    protected function grantModuleEditions(): void
    {
        if ($this->edition === self::EDITION_PRO) {
//            Forms::isEnabled() && Forms::getInstance()->grantEdition(Edition::PRO);
            ReportsModule::isEnabled() && ReportsModule::getInstance()->grantEdition(Edition::PRO);
        }
    }

    /**
     * @throws MigrationException
     */
    protected function afterInstall(): void
    {
        InstallHelper::runInstallMigrations($this);

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        // Redirect to welcome page
        $url = UrlHelper::cpUrl('sprout/welcome/forms');
        Craft::$app->getResponse()->redirect($url)->send();
    }

    /**
     * @throws MigrationException
     */
    protected function beforeUninstall(): void
    {
        InstallHelper::runUninstallMigrations($this);
    }
}

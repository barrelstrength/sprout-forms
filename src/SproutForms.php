<?php

namespace BarrelStrength\SproutForms;

use BarrelStrength\Sprout\core\db\MigrationHelper;
use BarrelStrength\Sprout\core\db\SproutPluginMigrationInterface;
use BarrelStrength\Sprout\core\db\SproutPluginMigrator;
use BarrelStrength\Sprout\core\editions\Edition;
use BarrelStrength\Sprout\core\modules\Modules;
use BarrelStrength\Sprout\datastudio\DataStudioModule;
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
    public string $minVersionRequired = '4.6.8';

    public string $schemaVersion = '0.0.1.3';

    /**
     * @inheritDoc
     */
    public static function editions(): array
    {
        return [
            Edition::LITE,
            Edition::PRO,
        ];
    }

    public static function getSchemaDependencies(): array
    {
        return [
            FormsModule::class,
            DataStudioModule::class,
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMigrator(): MigrationManager
    {
        return SproutPluginMigrator::make($this);
    }

    public function init()
    {
        parent::init();

        Event::on(
            Modules::class,
            Modules::EVENT_REGISTER_SPROUT_AVAILABLE_MODULES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = FormsModule::class;
                $event->types[] = DataStudioModule::class;
            }
        );

        $this->instantiateSproutModules();
        $this->grantModuleEditions();
    }

    protected function instantiateSproutModules(): void
    {
        FormsModule::isEnabled() && FormsModule::getInstance();
        DataStudioModule::isEnabled() && DataStudioModule::getInstance();
    }

    protected function grantModuleEditions(): void
    {
        if ($this->edition === Edition::PRO) {
//            Forms::isEnabled() && Forms::getInstance()->grantEdition(Edition::PRO);
            DataStudioModule::isEnabled() && DataStudioModule::getInstance()->grantEdition(Edition::PRO);
        }
    }

    /**
     * @throws MigrationException
     */
    protected function afterInstall(): void
    {
        MigrationHelper::runMigrations($this);

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
        MigrationHelper::runUninstallMigrations($this);
    }
}

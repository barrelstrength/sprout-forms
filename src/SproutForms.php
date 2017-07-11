<?php
namespace barrelstrength\sproutforms;

use barrelstrength\sproutcore\services\sproutreports\DataSources;
use barrelstrength\sproutforms\services\App;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;
use craft\events\DefineComponentsEvent;
use craft\web\twig\variables\CraftVariable;

use barrelstrength\sproutcore\SproutCoreHelper;
use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\variables\SproutFormsVariable;
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
use barrelstrength\sproutforms\integrations\sproutreports\datasources\SproutFormsEntriesDataSource;

class SproutForms extends \craft\base\Plugin
{
	/**
	 * Enable use of SproutForms::$app-> in place of Craft::$app->
	 *
	 * @var App
	 */
	public static $app;

	public $hasCpSection = true;
	public $hasCpSettings = true;

	public function init()
	{
		parent::init();

		self::$app = $this->get('app');
		SproutCoreHelper::registerModule();

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
				$event->fields[] = new Notes();
				$event->fields[] = new Phone();
				$event->fields[] = new RegularExpression();
			}
		);

		// Register DataSources for sproutReports plugin integration
		Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, function(RegisterComponentTypesEvent
		                                                                                 $event) {
			$event->types[] = new SproutFormsEntriesDataSource();
		});

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_DEFINE_COMPONENTS,
			function (DefineComponentsEvent $event) {
					$event->components['sproutforms'] = SproutFormsVariable::class;
			}
		);
	}

	public function getCpNavItem()
	{
		$parent = parent::getCpNavItem();
		$parent['url'] = 'sprout-forms';
		return array_merge($parent,[
			'subnav' => [
				'entries' => [
					"label" => SproutForms::t("Entries"),
					"url"   => 'sprout-forms/entries'
				],
				'forms' =>[
					"label" => SproutForms::t("Forms"),
					"url" => 'sprout-forms/forms'
				],
				'reports' =>[
					"label" => SproutForms::t("Reports"),
					"url" => 'sprout-forms/reports/sproutforms.sproutformsentriesdatasource'
				],
				'settings' =>[
					"label" => SproutForms::t("Settings"),
					"url" => 'sprout-forms/settings'
				]
			]
		]);
	}

	protected function createSettingsModel()
	{
		return new Settings();
	}

	/**
	 * @param string $message
	 * @param array  $params
	 *
	 * @return string
	 */
	public static function t($message, array $params = [])
	{
		return Craft::t('sprout-forms', $message, $params);
	}

	public static function error($message)
	{
		Craft::error($message, __METHOD__);
	}

	public static function info($message)
	{
		Craft::info($message, __METHOD__);
	}

	public static function warning($message)
	{
		Craft::warning($message, __METHOD__);
	}

	/**
	 * @return array
	 */
	private function getCpUrlRules()
	{
		return [
			'sprout-forms/forms/new'                                  =>
			'sprout-forms/forms/edit-form-template',

			'sprout-forms/forms/edit/<formId:\d+>'                    =>
			'sprout-forms/forms/edit-form-template',

			'sprout-forms/entries/edit/<entryId:\d+>'                 =>
			'sprout-forms/entries/edit-entry',

			'sprout-forms/settings/(general|advanced)'                =>
			'sprout-forms/settings/settings-index-template',

			'sprout-forms/settings/entrystatuses'                     =>
			'sprout-forms/entry-statuses/index',

			'sprout-forms/settings/entrystatuses/new'                 =>
			'sprout-forms/entry-statuses/edit',

			'sprout-forms/settings/entrystatuses/<entryStatusId:\d+>' =>
			'sprout-forms/entry-statuses/edit',

			'sprout-forms/forms/<groupId:\d+>'                        =>
			'sprout-forms/forms',

			'sprout-forms/reports/<dataSourceId>/new' => 'sprout-core/reports/edit-report',
			'sprout-forms/reports/<dataSourceId>/edit/<reportId>' => 'sprout-core/reports/edit-report',
			'sprout-forms/reports/view/<reportId>' => 'sprout-core/reports/results-index',
			'sprout-forms/reports/<dataSourceId>' => 'sprout-core/reports/index'
		];
	}

	/**
	 * @throws \Exception
	 */
	public function beforeUninstall(): bool
	{
		$forms = SproutForms::$app->forms->getAllForms();

		foreach ($forms as $form)
		{
			SproutForms::$app->forms->deleteForm($form);
		}

		return true;
	}
}


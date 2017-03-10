<?php
namespace barrelstrength\sproutforms;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\services\Groups;
use barrelstrength\sproutforms\variables\SproutFormsVariable;
use barrelstrength\sproutforms\events\RegisterFieldsEvent;
use barrelstrength\sproutforms\integrations\sproutforms\fields\PlainText;
use barrelstrength\sproutforms\services\Fields;

class SproutForms extends \craft\base\Plugin
{
	/**
	 * Enable use of SproutForms::$plugin-> in place of Craft::$app->
	 *
	 * @var [type]
	 */
	public static $api;

	public $hasCpSection = true;

	public function init()
	{
		parent::init();

		self::$api = $this->get('api');

		Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules, $this->getCpUrlRules());
			}
		);

		Event::on(Fields::class, Fields::EVENT_REGISTER_FIELDS, function(RegisterFieldsEvent $event) {
				$event->fields[] = new PlainText();
			}
		);
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
		return Craft::t('sproutForms', $message, $params);
	}

	public static function log($message, $type = 'info')
	{
		Craft::$type(self::t($message), __METHOD__);
	}

	/**
	 * @return array
	 */
	private function getCpUrlRules()
	{
		return [
			'sproutforms/forms'                                      =>
			'sprout-forms/forms/index',
			'sproutforms/forms/new'                                  =>
			'sprout-forms/forms/edit-form-template',

			'sprout-forms/forms/edit/<formId:\d+>'                    =>
			'sprout-forms/forms/edit-form-template',

			'sprout-forms/entries/edit/<entryId:\d+>'                 =>
			'sprout-forms/entries/edit-entry-template',

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
		];
	}

	/**
	 * @return string
	 */
	public function defineTemplateComponent()
	{
		return SproutFormsVariable::class;
	}
}


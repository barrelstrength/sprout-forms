<?php
namespace barrelstrength\sproutforms;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\services\Groups;
use barrelstrength\sproutforms\variables\SproutFormsVariable;

class SproutForms extends \craft\base\Plugin
{
	/**
	 * Enable use of SproutForms::$plugin-> in place of Craft::$app->
	 *
	 * @var [type]
	 */
	public static $api;

	public function init()
	{
		parent::init();

		self::$api = $this->get('api');

		Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules, $this->getCpUrlRules());
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

	/**
	 * @return bool
	 */
	public function hasCpSection()
	{
		return true;
	}

	/**
	 * @return array
	 */
	private function getCpUrlRules()
	{
		return [
			'sproutforms/forms/new'                                  =>
			'sprout-forms/forms/edit-form-template',

			'sproutforms/forms/edit/<formId:\d+>'                    =>
			'sprout-forms/forms/edit-form-template',

			'sproutforms/entries/edit/<entryId:\d+>'                 =>
			'sprout-forms/entries/edit-entry-template',

			'sproutforms/settings/(general|advanced)'                =>
			'sprout-forms/settings/settings-index-template',

			'sproutforms/settings/entrystatuses'                     =>
			'sprout-forms/entry-statuses/index',

			'sproutforms/settings/entrystatuses/new'                 =>
			'sprout-forms/entry-statuses/edit',

			'sproutforms/settings/entrystatuses/<entryStatusId:\d+>' =>
			'sprout-forms/entry-statuses/edit',

			'sproutforms/forms/<groupId:\d+>'                        =>
			'sproutforms/forms',
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


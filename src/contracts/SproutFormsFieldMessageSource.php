<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutforms\contracts;

use Craft;
use craft\db\Query;
use function GuzzleHttp\Promise\all;
use yii\base\Exception;

/**
 * Class PhpMessageSource
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class SproutFormsFieldMessageSource extends \yii\i18n\PhpMessageSource
{
	// Properties
	// =========================================================================

	/**
	 * @var bool Whether the messages can be overridden by translations in the site’s translations folder
	 */
	public $allowOverrides = true;

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	protected function loadMessages($category, $language)
	{
		$messages = parent::loadMessages($category, $language);

		if ($this->allowOverrides) {
			$overrideMessages = $this->_loadOverrideMessages($category, $language);
			$messages = array_merge($messages, $overrideMessages);
		}

		return $messages;
	}

	// Private Methods
	// =========================================================================

	/**
	 * Returns the override methods defined in the site’s translations folder.
	 *
	 * @param string $category
	 * @param string $language
	 *
	 * @return array|null
	 * @throws Exception
	 */
	private function _loadOverrideMessages(string $category, string $language)
	{
		$primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;
		$currentSiteId = Craft::$app->getSites()->currentSite->id;

		$primarySiteFieldTranslations = (new Query())
			->select(['*'])
			->from(['{{%sproutforms_fields_sites}}'])
			->where(['siteId' => $primarySiteId])
			->indexBy('id')
			->all();

		$currentSiteFieldTranslations = (new Query())
			->select(['*'])
			->from(['{{%sproutforms_fields_sites}}'])
			->where(['siteId' => $currentSiteId])
			->indexBy('id')
			->all();

		$translationMessages = [];

		foreach ($primarySiteFieldTranslations as $primaryTranslation) {
			$translationMessages[$primaryTranslation['name']] = $currentSiteFieldTranslations[$currentSiteId]['name'];
			$translationMessages[$primaryTranslation['instructions']] = $currentSiteFieldTranslations[$currentSiteId]['instructions'];
		}

		return $translationMessages;
	}
}

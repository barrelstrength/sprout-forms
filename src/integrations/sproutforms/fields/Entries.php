<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\elements\Entry;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsEntriesField
 *
 */
class Entries extends SproutBaseRelationField
{
	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return SproutForms::t('Entries');
	}

	/**
	 * @inheritdoc
	 */
	protected static function elementType(): string
	{
		return Entry::class;
	}

	/**
	 * @inheritdoc
	 */
	public static function defaultSelectionLabel(): string
	{
		return SproutForms::t('Add an Entry');
	}

	// Properties
	// =====================================================================

	/**
	 * @var string|null The input’s boostrap class
	 */
	public $boostrapClass;

	/**
	 * @param FieldModel $field
	 * @param mixed      $value
	 * @param array      $settings
	 * @param array      $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
	{
		$this->beginRendering();

		$entries = SproutForms::$app->frontEndFields->getFrontEndEntries($settings);

		$rendered = Craft::$app->getView()->renderTemplate(
			'entries/input',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions,
				'entries'          => $entries,
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		$parentRendered = parent::getSettingsHtml();

		$rendered = Craft::$app->getView()->renderTemplate(
			'sprout-forms/_components/fields/entries/settings',
			[
				'field' => $this,
			]
		);

		$customRendered = $rendered.$parentRendered;

		return $customRendered;
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-newspaper-o';
	}
}

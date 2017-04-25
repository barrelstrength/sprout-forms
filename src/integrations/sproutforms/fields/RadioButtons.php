<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\RadioButtons as CraftRadioButtons;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class SproutFormsRadioButtonsField
 *
 * @package Craft
 */
class RadioButtons extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return CraftRadioButtons::class;
	}

	/**
	 * @return bool
	 */
	public function hasMultipleLabels()
	{
		return true;
	}

	/**
	 * @param FieldModel $field
	 * @param mixed      $value
	 * @param array      $settings
	 * @param array      $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	public function getInputHtml($field, $value, $settings, array $renderingOptions = null)
	{
		$this->beginRendering();

		$rendered = Craft::$app->getView()->renderTemplate(
			'radiobuttons/input',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}

	/**
	 * @param FieldModel $field
	 *
	 * @return \Twig_Markup
	 */
	public function getSettingsHtml($field)
	{
		$rendered = Craft::$app->getView()->renderTemplate(
			'sproutforms/_components/fields/plaintext/settings',
			[
				'field' => $field,
			]
		);

		return $rendered;
	}
}

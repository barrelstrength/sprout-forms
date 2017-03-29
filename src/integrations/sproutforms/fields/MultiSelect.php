<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\MultiSelect as CraftMultiSelect;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class SproutFormsMultiSelectField
 *
 * @package Craft
 */
class MultiSelect extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return CraftMultiSelect::class;
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
			'multiselect/input',
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
}

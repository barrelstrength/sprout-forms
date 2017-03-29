<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\Number as CraftNumber;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class SproutFormsNumberField
 *
 * @package Craft
 */
class Number extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return CraftNumber::class;
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
			'number/input',
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

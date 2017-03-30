<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\Categories as CraftCategories;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class SproutFormsCategoriesField
 *
 * @package Craft
 */
class Categories extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return CraftCategories::class;
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

		$categories = SproutForms::$app->frontEndFields->getFrontEndCategories($settings);

		$rendered = Craft::$app->getView()->renderTemplate(
			'categories/input',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions,
				'categories'       => $categories,
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}
}

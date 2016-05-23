<?php
namespace Craft;

/**
 * Class SproutFormsCategoriesField
 *
 * @package Craft
 */
class SproutFormsCategoriesField extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Categories';
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

		$categories = sproutForms()->frontEndFields->getFrontEndCategories($settings);

		$rendered = craft()->templates->render(
			'categories/input',
			array(
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions,
				'categories'       => $categories,
			)
		);

		$this->endRendering();

		return TemplateHelper::getRaw($rendered);
	}

	/**
	 * @return string
	 */
	public function getTemplatesPath()
	{
		return craft()->path->getPluginsPath() . 'sproutforms/templates/_components/fields/';
	}
}

<?php
namespace Craft;

/**
 * Class SproutFormsCategoriesField
 *
 * @package Craft
 */
class SproutFormsEntriesField extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Entries';
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

		$entries = sproutForms()->frontEndFields->getFrontEndEntries($settings);

		$rendered = craft()->templates->render(
			'entries/input',
			array(
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions,
				'entries'          => $entries,
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

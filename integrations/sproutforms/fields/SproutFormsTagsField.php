<?php
namespace Craft;

/**
 * Class SproutFormsTagsField
 *
 * @package Craft
 */
class SproutFormsTagsField extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Tags';
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

		$tags = sproutForms()->frontEndFields->getFrontEndTags($settings);

		$rendered = craft()->templates->render(
			'tags/input',
			array(
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions,
				'tags'       => $tags,
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

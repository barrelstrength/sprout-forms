<?php
namespace Craft;

class Assets_SproutFormsFieldType extends BaseSproutFormsFieldType
{
	/**
	 * @param FieldModel $field
	 * @param mixed $value
	 * @param array $settings
	 *
	 * @return string
	 */
	public function getInputHtml($field, $value, $settings, $renderingOptions = null)
	{
		return craft()->templates->render('fields/assets/input', array(
			'name'           => $field->handle,
			'renderingOptions' => $renderingOptions,
		));
	}
}
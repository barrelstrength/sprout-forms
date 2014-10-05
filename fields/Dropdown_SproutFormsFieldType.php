<?php
namespace Craft;

/**
 *
 */
class Dropdown_SproutFormsFieldType extends BaseSproutFormsFieldType
{
	/**
	 * Returns the field's input HTML.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($field, $value, $settings)
	{
		return craft()->templates->render('fields/dropdown/input', array(
			'name'    => $field->handle,
			'options' => $settings->options,
			'value'   => $value,
		));
	}
}

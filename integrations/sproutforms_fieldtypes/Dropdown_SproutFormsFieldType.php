<?php
namespace Craft;

/**
 *
 */
class Dropdown_SproutFormsFieldType
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
		return craft()->templates->render('_macros/fieldtypes/Dropdown/input', array(
			'name'  => $field->handle,
			'options' => $settings->options,
			'values' => $value,
		));
	}
}

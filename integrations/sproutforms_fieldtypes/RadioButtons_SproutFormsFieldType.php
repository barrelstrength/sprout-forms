<?php
namespace Craft;

/**
 *
 */
class RadioButtons_SproutFormsFieldType
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
		return craft()->templates->render('_macros/fieldtypes/RadioButtons/input', array(
			'name'  => $field->handle,
			'options' => $settings->options,
			'values' => $value,
		));
	}
}

<?php
namespace Craft;

/**
 *
 */
class MultiSelect_SproutFormsFieldType
{
	/**
	 * Returns the field's input HTML.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($field, $settings)
	{
		return craft()->templates->render('_macros/fieldtypes/MultiSelect/input', array(
			'name'  => $field->handle,
			'options' => $settings->options,
			'values' => array(),
		));
	}
}

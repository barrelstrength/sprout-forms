<?php
namespace Craft;

/**
 *
 */
class Number_SproutFormsFieldType extends BaseSproutFormsFieldType
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
		return craft()->templates->render('fields/number/input', array(
			'name'  => $field->handle,
			'value' => $value,
			'size'  => 5
		));
	}
}

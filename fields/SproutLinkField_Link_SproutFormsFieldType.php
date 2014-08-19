<?php
namespace Craft;

/**
 *
 */
class SproutLinkField_Link_SproutFormsFieldType
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
		return craft()->templates->render('fields/SproutLinkField/input', array(
			'name'  => $field->handle,
			'value'=> $value,
		));
	}
}

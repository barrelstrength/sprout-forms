<?php
namespace Craft;

/**
 *
 */
class SproutEmailField_Email_SproutFormsFieldType
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
		return craft()->templates->render('_macros/fieldtypes/SproutEmailField/input', array(
			'name'  => $field->handle,
			'value'=> craft()->request->getPost($field->handle),
		));
	}
}

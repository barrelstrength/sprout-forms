<?php
namespace Craft;

/**
 *
 */
class PlainText_SproutFormsFieldType
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
		return craft()->templates->render('_macros/fieldtypes/PlainText/input', array(
			'name' => $field->handle,
			'value'=> craft()->request->getPost($field->handle),
			'settings' => $settings
		));
	}
}

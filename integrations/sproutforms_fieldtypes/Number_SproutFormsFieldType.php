<?php
namespace Craft;

/**
 *
 */
class Number_SproutFormsFieldType
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
		return craft()->templates->render('_macros/fieldtypes/Number/input', array(
			'name'  => $field->handle,
			'value'=> craft()->request->getPost($field->handle),
			'size' => 5
		));
	}
}

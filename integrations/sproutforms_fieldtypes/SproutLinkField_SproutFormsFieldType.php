<?php
namespace Craft;

/**
 *
 */
class SproutLinkField_SproutFormsFieldType
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
		return craft()->templates->render('_macros/fieldtypes/SproutLinkField/input', array(
			'name'  => $field->handle,
			'value'=> craft()->request->getPost($field->handle),
		));
	}
}

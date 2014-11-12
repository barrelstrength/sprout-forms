<?php
namespace Craft;

/**
 *
 */
class BaseSproutFormsFieldType implements ISproutFormsFieldType
{
	
	public $isNakedField = false;

	/**
	 * Returns the field's input HTML.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($field, $value, $settings)
	{
		return "";
	}

	/**
	 * Default to use the 'fields' namespace for all custom fields
	 * 
	 * @return string  Custom fields namespace
	 */
	public function getNamespace()
	{
		return "fields";
	}

}

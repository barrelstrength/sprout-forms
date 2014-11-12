<?php
namespace Craft;

/**
 *
 */
interface ISproutFormsFieldType
{
	/**
	 * Returns the field's input HTML.
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param mixed  $value
	 * @return string
	 */
	public function getInputHtml($field, $value, $settings);

	/**
	 * Default to use the 'fields' namespace for all custom fields
	 * 
	 * @return string  Custom fields namespace
	 */
	public function getNamespace();

}

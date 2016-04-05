<?php
namespace Craft;

/**
 * Class SproutFormsBaseField
 *
 * @package Craft
 */
abstract class SproutFormsBaseField
{
	/**
	 * @var array
	 */
	protected static $fieldVariables = array();

	/**
	 * @var string
	 */
	protected $originalTemplatesPath;

	/**
	 * @return string
	 */
	abstract public function getType();

	/**
	 * @param FieldModel $field
	 * @param mixed      $value
	 * @param mixed      $settings
	 * @param array      $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	abstract public function getInputHtml($field, $value, $settings, array $renderingOptions = null);

	final public function beginRendering()
	{
		$this->originalTemplatesPath = craft()->templates->getTemplatesPath();

		craft()->templates->setTemplatesPath($this->getTemplatesPath());
	}

	final public function endRendering()
	{
		craft()->templates->setTemplatesPath($this->originalTemplatesPath);
	}

	final public function setValue($handle, $value)
	{
		craft()->httpSession->add($handle, $value);
	}

	final public function getValue($handle, $default = null)
	{
		return craft()->httpSession->get($handle, $default);
	}

	public static function addFieldVariables(array $variables)
	{
		static::$fieldVariables = array_merge(static::$fieldVariables, $variables);
	}

	public static function getFieldVariables()
	{
		return static::$fieldVariables;
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return 'fields';
	}

	/**
	 * @return string
	 */
	public function getTemplatesPath()
	{
		return craft()->path->getPluginsPath() . 'sproutforms/templates/_components/fields/';
	}

	/**
	 * Tells Sprout Forms NOT to wrap your getInputHtml() content inside any extra HTML
	 *
	 * @return bool
	 */
	public function isPlainInput()
	{
		return false;
	}

	/**
	 * Tells Sprout Forms NOT to add a (for) attribute to your field's top leve label
	 *
	 * @note
	 * Sprout Forms renders a label with a (for) attribute for all fields.
	 * If your field has multiple labels, like radio buttons do for example,
	 * it would make sense for your field no to have a (for) attribute at the top level
	 * but have them at the radio field level
	 */
	public function hasMultipleLabels()
	{
		return false;
	}
}

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
	 * @var mixed
	 */
	protected $context;

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
	 * @param mixed $value
	 * @param mixed $settings
	 * @param array $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	abstract public function getInputHtml($field, $value, $settings, array $renderingOptions = null);

	final public function beginRendering()
	{
		$this->originalTemplatesPath = craft()->path->getTemplatesPath();

		craft()->path->setTemplatesPath($this->getTemplatesPath());
	}

	final public function endRendering()
	{
		craft()->path->setTemplatesPath($this->originalTemplatesPath);
	}

	final public function setValue($handle, $value)
	{
		craft()->httpSession->add($handle, $value);
	}

	final public function getValue($handle, $default = null)
	{
		return craft()->httpSession->get($handle, $default);
	}

	final public function setContext($context)
	{
		$this->context = $context;
	}

	final public function getContext()
	{
		return $this->context;
	}

	/**
	 * @return string
	 */
	public function getNamespace()
	{
		return 'fields';
	}

	/**
	 * @return bool
	 */
	public function needsGlobalContext()
	{
		return false;
	}

	/**
	 * @return string
	 */
	public function getTemplatesPath()
	{
		return craft()->path->getPluginsPath().'sproutforms/templates/_components/fields/';
	}

	public function isPlainInput()
	{
		return false;
	}
}

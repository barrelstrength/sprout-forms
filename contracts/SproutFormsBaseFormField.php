<?php
namespace Craft;

/**
 * Class SproutFormsBaseFormField
 *
 * @package Craft
 */
abstract class SproutFormsBaseFormField
{
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
		return craft()->path->getPluginsPath().'sproutforms/templates/_components/fields/';
	}

	public function isPlainInput()
	{
		return false;
	}
}

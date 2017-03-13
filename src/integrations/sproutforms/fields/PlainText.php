<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\PlainText as CraftPlainText;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class PlainText
 *
 * @package Craft
 */
class PlainText extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return CraftPlainText::class;
	}

	/**
	 * @param FieldModel $field
	 * @param mixed      $value
	 * @param array      $settings
	 * @param array      $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	public function getInputHtml($field, $value, $settings, array $renderingOptions = null)
	{
		$this->beginRendering();

		$as = Craft::$app->getView()->getTemplatesPath();

		$rendered = Craft::$app->getView()->render(
			'plaintext/input',
			array(
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions
			)
		);

		$this->endRendering();

		return TemplateHelper::getRaw($rendered);
	}

	/**
	 * @return string
	 */
	public function getTemplatesPath()
	{
		return Craft::$app->path->getPluginsPath() . 'sproutforms/src/templates/_components/fields/';
	}
}

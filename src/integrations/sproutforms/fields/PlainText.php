<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class PlainText
 *
 * @package Craft
 */
class PlainText extends SproutFormsBaseField
{
	/**
	 * @var string|null The input’s placeholder text
	 */
	public $placeholder;

	/**
	 * @var bool|null Whether the input should allow line breaks
	 */
	public $multiline;

	/**
	 * @var int The minimum number of rows the input should have, if multi-line
	 */
	public $initialRows = 4;

	/**
	 * @var int|null The maximum number of characters allowed in the field
	 */
	public $charLimit;

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

		$rendered = Craft::$app->getView()->renderTemplate(
			'plaintext/input',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}

	/**
	 * @param FieldModel $field
	 *
	 * @return \Twig_Markup
	 */
	public function getSettingsHtml($field)
	{
		$this->beginRendering();

		$rendered = Craft::$app->getView()->renderTemplate(
			'plaintext/settings',
			[
				'field' => $field,
			]
		);

		$this->endRendering();

		return $rendered;
	}
}

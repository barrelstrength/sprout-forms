<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutforms\SproutForms;

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
	public $boostrapClass;

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
	 * @var string The type of database column the field should have in the content table
	 */
	public $columnType = Schema::TYPE_TEXT;

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return SproutForms::t('Plain Text');
	}

	/**
	 * @param FieldModel $field
	 * @param mixed      $value
	 * @param array      $settings
	 * @param array      $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
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
	public function getSettingsHtml()
	{
		$rendered = Craft::$app->getView()->renderTemplate(
			'sproutforms/_components/fields/plaintext/settings',
			[
				'field' => $this,
			]
		);

		return $rendered;
	}
}

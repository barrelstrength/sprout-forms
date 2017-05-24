<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutforms\SproutForms;

/**
 * Class PlainText
 *
 * @package Craft
 */
class PlainText extends SproutFormsBaseField implements PreviewableFieldInterface
{
	/**
	 * @var string|null The input’s placeholder text
	 */
	public $boostrapClass;

	/**
	 * @var string|null The input’s placeholder text
	 */
	public $placeholder = '';

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
	 * Adds support for edit field in the Entries section of SproutForms (Control
	 * panel html)
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		return Craft::$app->getView()->renderTemplate('_components/fieldtypes/PlainText/input',
			[
				'name' => $this->handle,
				'value' => $value,
				'field' => $this,
			]);
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

	/**
	 * @return string
	 */
	public function getIconPath()
	{
		return $this->getTemplatesPath().'plaintext/input.svg';
	}
}

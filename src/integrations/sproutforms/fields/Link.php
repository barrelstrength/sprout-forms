<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutcore\SproutCore;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutcore\web\assets\sproutfields\link\LinkFieldAsset;

class Link extends SproutFormsBaseField implements PreviewableFieldInterface
{

	/**
	 * @var string|null
	 */
	public $customPatternErrorMessage;

	/**
	 * @var bool|null
	 */
	public $customPatternToggle;

	/**
	 * @var string|null
	 */
	public $customPattern;

	/**
	 * @var string|null
	 */
	public $placeholder;

	public static function displayName(): string
	{
		return SproutForms::t('Link');
	}

	/**
	 * @inheritdoc
	 */
	public function getContentColumnType(): string
	{
		return Schema::TYPE_STRING;
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-link';
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		return Craft::$app->getView()->renderTemplate(
			'sprout-forms/_components/fields/link/settings',
			[
				'field' => $this,
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$view = Craft::$app->getView();
		$view->registerAssetBundle(LinkFieldAsset::class);

		$name = $this->handle;
		$inputId = Craft::$app->getView()->formatInputId($name);
		$namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

		$fieldContext = SproutCore::$app->utilities->getFieldContext($this, $element);

		return Craft::$app->getView()->renderTemplate('sprout-core/sproutfields/_includes/forms/link/input', [
				'namespaceInputId' => $namespaceInputId,
				'id' => $inputId,
				'name' => $name,
				'value' => $value,
				'fieldContext' => $fieldContext,
				'placeholder' => $this->placeholder
			]
		);
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

		$attributes = $field->getAttributes();
		$errorMessage = SproutCore::$app->link->getErrorMessage($attributes['name'], $settings);
		$placeholder = (isset($settings['placeholder'])) ? $settings['placeholder'] : '';

		$rendered = Craft::$app->getView()->renderTemplate(
			'link/input',
			[
				'name' => $field->handle,
				'value' => $value,
				'field' => $field,
				'pattern' => $settings['customPattern'],
				'errorMessage' => $errorMessage,
				'renderingOptions' => $renderingOptions,
				'placeholder' => $placeholder
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}

	/**
	 * @inheritdoc
	 */
	public function getElementValidationRules(): array
	{
		$rules = parent::getElementValidationRules();
		$rules[] = 'validateLink';

		return $rules;
	}

	/**
	 * Validates our fields submitted value beyond the checks
	 * that were assumed based on the content attribute.
	 *
	 *
	 * @param ElementInterface $element
	 *
	 * @return void
	 */
	public function validateLink(ElementInterface $element)
	{
		$value = $element->getFieldValue($this->handle);

		$handle = $this->handle;
		$name = $this->name;

		if (!SproutCore::$app->link->validate($value, $this))
		{
			$element->addError(
				$this->handle,
				SproutCore::$app->link->getErrorMessage($this->name, $this)
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getTableAttributeHtml($value, ElementInterface $element): string
	{
		$html = '';

		if ($value)
		{
			$html = '<a href="'.$value.'" target="_blank">'.$value.'</a>';
		}

		return $html;
	}
}

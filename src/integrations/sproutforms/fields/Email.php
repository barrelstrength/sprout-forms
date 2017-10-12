<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutcore\SproutCore;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

class Email extends SproutFormsBaseField implements PreviewableFieldInterface
{
	/**
	 * @var string|null
	 */
	public $customPattern;

	/**
	 * @var bool
	 */
	public $customPatternToggle;

	/**
	 * @var string|null
	 */
	public $customPatternErrorMessage;

	/**
	 * @var bool
	 */
	public $uniqueEmail;

	/**
	 * @var string|null
	 */
	public $placeholder;

	public static function displayName(): string
	{
		return SproutForms::t('Email Address');
	}

	/**
	 * @inheritdoc
	 */
	public function getContentColumnType(): string
	{
		return Schema::TYPE_STRING;
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/email/settings',
			[
				'field' => $this,
			]);
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-envelope';
	}

	/**
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$name = $this->handle;
		$inputId = Craft::$app->getView()->formatInputId($name);
		$namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

		$fieldContext = SproutCore::$app->utilities->getFieldContext($this, $element);

		// Set this to false for Quick Entry Dashboard Widget
		$elementId = ($element != null) ? $element->id : false;

		$rendered = Craft::$app->getView()->renderTemplate(
			'sprout-core/sproutfields/_includes/forms/email/input',
			[
				'namespaceInputId' => $namespaceInputId,
				'id' => $inputId,
				'name' => $name,
				'value' => $value,
				'elementId' => $elementId,
				'fieldContext' => $fieldContext,
				'placeholder' => $this->placeholder
			]);

		return TemplateHelper::raw($rendered);
	}

	/**
	 * @param \barrelstrength\sproutforms\contracts\FieldModel $field
	 * @param mixed                                            $value
	 * @param mixed                                            $settings
	 * @param array|null                                       $renderingOptions
	 *
	 * @return string
	 */
	public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
	{
		$this->beginRendering();

		$attributes = $field->getAttributes();
		$errorMessage = SproutCore::$app->email->getErrorMessage($attributes['name'], $settings);
		$placeholder = (isset($settings['placeholder'])) ? $settings['placeholder'] : '';

		$rendered = Craft::$app->getView()->renderTemplate(
			'email/forminput',
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
		$rules[] = 'validateEmail';

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
	public function validateEmail(ElementInterface $element)
	{
		$value = $element->getFieldValue($this->handle);

		$customPattern = $this->customPattern;
		$checkPattern = $this->customPatternToggle;

		if (!SproutCore::$app->email->validateEmailAddress($value, $customPattern, $checkPattern))
		{
			$element->addError($this->handle,
				SproutCore::$app->email->getErrorMessage(
					$this->name, $this)
			);
		}

		$uniqueEmail = $this->uniqueEmail;

		if ($uniqueEmail && !SproutCore::$app->email->validateUniqueEmailAddress($value, $element, $this))
		{
			$element->addError($this->handle,
				SproutForms::t($this->name.' must be a unique email.')
			);
		}
	}
}

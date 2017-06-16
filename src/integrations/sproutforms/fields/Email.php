<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutcore\web\sproutfields\emailfield\EmailFieldAsset;

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
		return Craft::$app->getView()->renderTemplate('sproutforms/_components/fields/email/settings',
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
		$view = Craft::$app->getView();
		$view->registerAssetBundle(EmailFieldAsset::class);

		$name = $this->handle;
		$inputId          = Craft::$app->getView()->formatInputId($name);
		$namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

		$fieldContext = SproutForms::$app->utilities->getFieldContext($this, $element);

		// Set this to false for Quick Entry Dashboard Widget
		$elementId = ($element != null) ? $element->id : false;

		$template = $this->getTemplatesPath();

		$rendered = $view->renderTemplate('sprout-core/_integrations/sproutfields/fields/email/input',
			[
				'id'           => $namespaceInputId,
				'name'         => $name,
				'value'        => $value,
				'elementId'    => $elementId,
				'fieldContext' => $fieldContext,
				'placeholder'  => $this->placeholder
			]);

		return TemplateHelper::raw($rendered);
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

		$attributes   = $field->getAttributes();
		$errorMessage = SproutForms::$app->email->getErrorMessage($attributes['name'], $settings);
		$placeholder  = (isset($settings['placeholder'])) ? $settings['placeholder'] : '';

		$rendered = Craft::$app->getView()->renderTemplate(
			'email/forminput',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'pattern'          => $settings['customPattern'],
				'errorMessage'     => $errorMessage,
				'renderingOptions' => $renderingOptions,
				'placeholder'      => $placeholder
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
		$checkPattern  = $this->customPatternToggle;

		if (!SproutForms::$app->email->validateEmailAddress($value, $customPattern, $checkPattern))
		{
			$element->addError($this->handle,
				SproutForms::$app->email->getErrorMessage(
					$this->name, $this)
			);
		}

		$uniqueEmail = $this->uniqueEmail;

		if ($uniqueEmail && !SproutForms::$app->email->validateUniqueEmailAddress($value, $element, $this))
		{
			$element->addError($this->handle,
				SproutForms::t($this->name . ' must be a unique email.')
			);
		}
	}
}

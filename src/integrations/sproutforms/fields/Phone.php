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
use barrelstrength\sproutcore\web\assets\sproutfields\phone\PhoneFieldAsset;

class Phone extends SproutFormsBaseField implements PreviewableFieldInterface
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
	 * @var bool|null
	 */
	public $inputMask;

	/**
	 * @var string|null
	 */
	public $mask;

	/**
	 * @var string|null
	 */
	public $placeholder;

	public static function displayName(): string
	{
		return SproutForms::t('Phone Number');
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
		return 'fa fa-phone';
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		return Craft::$app->getView()->renderTemplate(
			'sprout-forms/_components/fields/phone/settings',
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
		$view->registerAssetBundle(PhoneFieldAsset::class);
		$name = $this->handle;
		$inputId          = Craft::$app->getView()->formatInputId($name);
		$namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

		return Craft::$app->getView()->renderTemplate(
			'sprout-core/sproutfields/_includes/forms/phone/input',
			[
				'id'    => $namespaceInputId,
				'name'  => $this->handle,
				'value' => $value,
				'field' => $this
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

		$name             = $field->handle;
		$namespaceInputId = $this->getNamespace() . '-' . $name;
		$mask = $settings['mask'];

		$mask = preg_quote($settings['mask']);
		// Do no escape "-" html5 does not treat it as special chars
		$mask = str_replace("\\-", '-', $mask);
		$pattern = SproutCore::$app->phone->convertMaskToRegEx($mask);

		$pattern = trim($pattern, '/');

		$attributes   = $field->getAttributes();
		$errorMessage = SproutCore::$app->phone->getErrorMessage($field);

		$rendered = Craft::$app->getView()->renderTemplate(
			'phone/forminput',
			[
				'name'             => $name,
				'value'            => $value,
				'settings'         => $settings,
				'field'            => $field,
				'pattern'          => $pattern,
				'errorMessage'     => $errorMessage,
				'namespaceInputId' => $namespaceInputId,
				'renderingOptions' => $renderingOptions
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
		$rules[] = 'validatePhone';

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
	public function validatePhone(ElementInterface $element)
	{
		$value = $element->getFieldValue($this->handle);

		$handle  = $this->handle;
		$name    = $this->name;

		if ($this->mask == "")
		{
			$this->mask = SproutCore::$app->phone->getDefaultMask();
		}

		if (!SproutCore::$app->phone->validate($value, $this->mask))
		{
			$element->addError(
				$this->handle,
				SproutCore::$app->phone->getErrorMessage($this)
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
			$formatter = Craft::$app->getFormatter();

			$html = '<a href="tel:' . $value . '" target="_blank">' . $value . '</a>';
		}

		return $html;
	}
}

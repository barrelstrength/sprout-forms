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
use barrelstrength\sproutcore\web\assets\sproutfields\regularexpression\RegularExpressionFieldAsset;

class RegularExpression extends SproutFormsBaseField implements PreviewableFieldInterface
{
	/**
	 * @var string
	 */
	public $customPatternErrorMessage;

	/**
	 * @var string
	 */
	public $customPattern;

	/**
	 * @var string
	 */
	public $placeholder;

	public static function displayName(): string
	{
		return SproutForms::t('Regular Expression');
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
		return 'fa fa-asterisk';
	}

	/**
	 * @inheritdoc
	 */
	public function getExampleInputHtml()
	{
		return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/regularexpression/example',
			[
				'field' => $this
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		return Craft::$app->getView()->renderTemplate(
			'sprout-forms/_components/fields/regularexpression/settings',
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
		$view->registerAssetBundle(RegularExpressionFieldAsset::class);

		$name = $this->handle;
		$inputId          = Craft::$app->getView()->formatInputId($name);
		$namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

		$fieldContext = SproutCore::$app->utilities->getFieldContext($this, $element);

		return Craft::$app->getView()->renderTemplate(
			'sprout-core/sproutfields/_includes/forms/regularexpression/input',
			[
				'id'           => $namespaceInputId,
				'field'        => $this,
				'name'         => $name,
				'value'        => $value,
				'fieldContext' => $fieldContext,
				'placeholder'  => $this->placeholder
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

		$placeholder  = (isset($settings['placeholder'])) ? $settings['placeholder'] : '';

		$pattern = $settings['customPattern'];

		// Do no escape "-" html5 does not treat it as special chars
		$pattern = str_replace("\\-", '-', $pattern);

		$rendered = Craft::$app->getView()->renderTemplate(
			'regularexpression/input',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'pattern'          => $pattern,
				'errorMessage'     => $settings['customPatternErrorMessage'],
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
		$rules[] = 'validateRegularExpression';

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
	public function validateRegularExpression(ElementInterface $element)
	{
		$value = $element->getFieldValue($this->handle);

		$handle  = $this->handle;
		$name    = $this->name;

		if (!SproutCore::$app->regularExpression->validate($value, $this))
		{
			$element->addError(
				$this->handle,
				SproutCore::$app->regularExpression->getErrorMessage($this)
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getTableAttributeHtml($value, ElementInterface $element): string
	{
		return $value;
	}
}

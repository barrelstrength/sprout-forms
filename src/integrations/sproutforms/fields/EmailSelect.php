<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;
use craft\helpers\ArrayHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutcore\SproutCore;

class EmailSelect extends SproutBaseOptionsField
{
	public static function displayName(): string
	{
		return SproutForms::t('Email Select');
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
	protected function optionsSettingLabel(): string
	{
		return SproutForms::t('Dropdown Options');
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-share';
	}

	/**
	 * @inheritdoc
	 */
	public function getExampleInputHtml()
	{
		return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/emailselect/example',
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
		$options = $this->options;

		if (!$options)
		{
			$options = [['label' => '', 'value' => '']];
		}

		return Craft::$app->getView()->renderTemplateMacro('_includes/forms',
			'editableTableField',
			[
				[
					'label'        => $this->optionsSettingLabel(),
					'instructions' => SproutForms::t('Define the available options.'),
					'id'           => 'options',
					'name'         => 'options',
					'addRowLabel'  => SproutForms::t('Add an option'),
					'cols'         => [
						'label'   => [
							'heading'      => SproutForms::t('Name'),
							'type'         => 'singleline',
							'autopopulate' => 'value'
						],
						'value'   => [
							'heading' => SproutForms::t('Email'),
							'type'    => 'singleline',
							'class'   => 'code'
						],
						'default' => [
							'heading' => SproutForms::t('Default?'),
							'type'    => 'checkbox',
							'class'   => 'thin'
						],
					],
					'rows' => $options
				]
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$valueOptions = $value->getOptions();
		$anySelected  = SproutCore::$app->utilities->isAnyOptionsSelected(
			$valueOptions,
			$value->value
		);

		$name  = $this->handle;
		$value = $value->value;

		if ($anySelected === false)
		{
			$value = $this->defaultValue();
		}

		$options = $this->options;

		return Craft::$app->getView()->renderTemplate('sprout-core/sproutfields/_includes/forms/emailselect/input',
			[
				'name'    => $name,
				'value'   => $value,
				'options' => $options
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

		$selectedValue = isset($value->value) ? $value->value : null;

		$options = $settings['options'];
		$options = SproutCore::$app->emailSelect->obfuscateEmailAddresses($options, $selectedValue);

		$rendered = Craft::$app->getView()->renderTemplate(
			'emailselect/input',
			[
				'name'     => $field->handle,
				'value'    => $value,
				'options'  => $options,
				'settings' => $settings,
				'field'    => $field
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
		$rules[] = 'validateEmailSelect';

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
	public function validateEmailSelect(ElementInterface $element)
	{
		$value = $element->getFieldValue($this->handle)->value;

		$emailAddresses = ArrayHelper::toArray($value);

		$emailAddresses = array_unique($emailAddresses);

		if (count($emailAddresses))
		{
			$invalidEmails = array();
			foreach ($emailAddresses as $emailAddress)
			{
				if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL))
				{
					$invalidEmails[] = SproutForms::t(
						'sproutFields',
						$emailAddress . " email does not validate"
					);
				}
			}
		}

		if (!empty($invalidEmails))
		{
			foreach ($invalidEmails as $invalidEmail)
			{
				$element->addError($this->handle, $invalidEmail, $this);
			}
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
			$html = $value->label . ': <a href="mailto:' . $value . '" target="_blank">' . $value . '</a>';
		}

		return $html;
	}
}

<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsCheckboxesField
 *
 */
class Checkboxes extends SproutBaseOptionsField
{
	/**
	 * @var string|null The input’s boostrap class
	 */
	public $boostrapClass;

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return SproutForms::t('Checkboxes');
	}

	/**
	 * @return bool
	 */
	public function hasMultipleLabels()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->multi = true;
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
			'checkboxes/input',
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
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$options = $this->translatedOptions();

		// If this is a new entry, look for any default options
		if ($this->isFresh($element)) {
			$value = $this->defaultValue();
		}

		return Craft::$app->getView()->renderTemplate('_includes/forms/checkboxGroup',
			[
				'name' => $this->handle,
				'values' => $value,
				'options' => $options
			]);
	}

	/**
	 * @inheritdoc
	 */
	protected function optionsSettingLabel(): string
	{
		return SproutForms::t('Checkbox Options');
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-check-square';
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		$parentRendered = parent::getSettingsHtml();

		$rendered = Craft::$app->getView()->renderTemplate(
			'sprout-forms/_components/fields/checkboxes/settings',
			[
				'field' => $this,
			]
		);

		$customRendered = $rendered.$parentRendered;

		return $customRendered;
	}
}

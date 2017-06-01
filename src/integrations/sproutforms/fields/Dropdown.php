<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsDropdownField
 *
 */
class Dropdown extends SproutBaseOptionsField
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
		return SproutForms::t('Dropdown');
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
			'dropdown/input',
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
		$options = $this->translatedOptions();

		// If this is a new entry, look for a default option
		if ($this->isFresh($element)) {
				$value = $this->defaultValue();
		}

		return Craft::$app->getView()->renderTemplate('_includes/forms/select',
			[
				'name' => $this->handle,
				'value' => $value,
				'options' => $options
			]
		);
	}

	/**
	 * @inheritdoc
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
		return 'fa fa-chevron-circle-down';
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		$parentRendered = parent::getSettingsHtml();

		$rendered = Craft::$app->getView()->renderTemplate(
			'sproutforms/_components/fields/dropdown/settings',
			[
				'field' => $this,
			]
		);

		$customRendered = $rendered.$parentRendered;

		return $customRendered;
	}

}

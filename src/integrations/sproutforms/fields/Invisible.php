<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

class Invisible extends SproutFormsBaseField implements PreviewableFieldInterface
{
	/**
	 * @var basename(path)ool
	 */
	public $allowEdits;

	/**
	 * @var bool
	 */
	public $hideValue;

	/**
	 * @var string|null
	 */
	public $value;

	public static function displayName(): string
	{
		return SproutForms::t('Invisible');
	}

	/**
	 * @return bool
	 */
	public function isPlainInput()
	{
		return true;
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
		return Craft::$app->getView()->renderTemplate(
			'sprout-forms/_components/fields/invisible/settings',
			[
				'field' => $this,
			]
		);
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-eye-slash';
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
		Craft::$app->getSession()->set($field->handle, $settings['value']);

		return TemplateHelper::raw(sprintf('<input type="hidden" name="%s" />', $field->handle));
	}


	/**
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$name             = $this->handle;
		$inputId          = Craft::$app->getView()->formatInputId($name);
		$namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

		return Craft::$app->getView()->renderTemplate(
			'sprout-core/sproutfields/fields/invisible/input',
			[
				'id'           => $namespaceInputId,
				'name'         => $name,
				'value'        => $value,
				'field'     => $this
			]
		);
	}


	/**
	 * @inheritdoc
	 */
	public function normalizeValue($value, ElementInterface $element = null)
	{
		$value = Craft::$app->getSession()->get($this->handle);
		$value = $value ? $value : '';
		$value = Craft::$app->view->renderObjectTemplate($value, parent::getFieldVariables());

		return parent::normalizeValue($value, $element);
	}

	/**
	 * @inheritdoc
	 */
	public function getTableAttributeHtml($value, ElementInterface $element): string
	{
		$hiddenValue = "";

		if ($value != "")
		{
			$hiddenValue = $this->hideValue ? "&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" : $value;
		}

		return $hiddenValue;
	}
}

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

class Hidden extends SproutFormsBaseField implements PreviewableFieldInterface
{
	/**
	 * @var bool
	 */
	public $allowEdits = false;

	/**
	 * @var string|null The maximum allowed number
	 */
	public $value = '';

	public function isPlainInput()
	{
		return true;
	}

	public static function displayName(): string
	{
		return SproutForms::t('Hidden');
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
		return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/hidden/settings',
			[
				'field' => $this,
			]);
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-user-secret';
	}

	/**
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		return Craft::$app->getView()->renderTemplate('sprout-core/sproutfields/_includes/forms/hidden/input',
			[
				'id'    => $this->handle,
				'name'  => $this->handle,
				'value' => $value,
				'field' => $this
			]);
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

		if ($settings['value'])
		{
			try
			{
				$value = Craft::$app->view->renderObjectTemplate($settings['value'], parent::getFieldVariables());
			}
			catch (\Exception $e)
			{
				SproutForms::error($e->getMessage());
			}
		}

		$rendered = Craft::$app->getView()->renderTemplate(
			'hidden/forminput',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'renderingOptions' => $renderingOptions
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}
}

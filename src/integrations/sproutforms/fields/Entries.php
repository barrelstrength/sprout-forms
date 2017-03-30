<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\Entries as CraftEntries;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class SproutFormsEntriesField
 *
 * @package Craft
 */
class Entries extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return CraftEntries::class;
	}

	/**
	 * @param FieldModel $field
	 * @param mixed      $value
	 * @param array      $settings
	 * @param array      $renderingOptions
	 *
	 * @return \Twig_Markup
	 */
	public function getInputHtml($field, $value, $settings, array $renderingOptions = null)
	{
		$this->beginRendering();

		$entries = SproutForms::$app->frontEndFields->getFrontEndEntries($settings);

		$rendered = Craft::$app->getView()->renderTemplate(
			'entries/input',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions,
				'entries'          => $entries,
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}
}

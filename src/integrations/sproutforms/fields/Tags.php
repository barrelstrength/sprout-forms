<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\fields\Tags as CraftTags;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * Class SproutFormsTagsField
 *
 * @package Craft
 */
class Tags extends SproutFormsBaseField
{
	/**
	 * @return string
	 */
	public function getType()
	{
		return CraftTags::class;
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

		$tags = SproutForms::$app->frontEndFields->getFrontEndTags($settings);

		$rendered = Craft::$app->getView()->renderTemplate(
			'tags/input',
			[
				'name'             => $field->handle,
				'value'            => $value,
				'field'            => $field,
				'settings'         => $settings,
				'renderingOptions' => $renderingOptions,
				'tags'             => $tags,
			]
		);

		$this->endRendering();

		return TemplateHelper::raw($rendered);
	}
}

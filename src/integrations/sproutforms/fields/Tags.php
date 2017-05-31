<?php
namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\elements\Tag;
use craft\elements\db\ElementQueryInterface;
use craft\models\TagGroup;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsTagsField
 *
 */
class Tags extends BaseRelationField
{
	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return SproutForms::t('Tags');
	}

	/**
	 * @inheritdoc
	 */
	protected static function elementType(): string
	{
		return Tag::class;
	}

	/**
	 * @inheritdoc
	 */
	public static function defaultSelectionLabel(): string
	{
		return SproutForms::t('Add a Tag');
	}

	// Properties
	// =====================================================================

	/**
	 * @var string|null The input’s boostrap class
	 */
	public $boostrapClass;

	/**
	 * @var
	 */
	private $_tagGroupId;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->allowMultipleSources = false;
		$this->allowLimit = false;
	}

	/**
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		/** @var Element|null $element */
		if (!($value instanceof ElementQueryInterface)) {
			/** @var Element $class */
			$class = static::elementType();
			$value = $class::find()
				->id(false);
		}

		$tagGroup = $this->_getTagGroup();

		if ($tagGroup) {
			return Craft::$app->getView()->renderTemplate('_components/fieldtypes/Tags/input',
				[
					'elementType' => static::elementType(),
					'id' => Craft::$app->getView()->formatInputId($this->handle),
					'name' => $this->handle,
					'elements' => $value,
					'tagGroupId' => $this->_getTagGroupId(),
					'targetSiteId' => $this->targetSiteId($element),
					'sourceElementId' => $element !== null ? $element->id : null,
					'selectionLabel' => $this->selectionLabel ? Craft::t('site', $this->selectionLabel) : static::defaultSelectionLabel(),
				]);
		} else {
			return '<p class="error">'.Craft::t('app', 'This field is not set to a valid source.').'</p>';
		}
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

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		$parentRendered = parent::getSettingsHtml();

		$rendered = Craft::$app->getView()->renderTemplate(
			'sproutforms/_components/fields/tags/settings',
			[
				'field' => $this,
			]
		);

		$customRendered = $rendered.$parentRendered;

		return $customRendered;
	}

	/**
	 * @return string
	 */
	public function getIconClass()
	{
		return 'fa fa-tags';
	}

	// Private Methods
	// =======================================================================

	/**
	 * Returns the tag group associated with this field.
	 *
	 * @return TagGroup|null
	 */
	private function _getTagGroup()
	{
		$tagGroupId = $this->_getTagGroupId();

		if ($tagGroupId !== false) {
			return Craft::$app->getTags()->getTagGroupById($tagGroupId);
		}

		return null;
	}

	/**
	 * Returns the tag group ID this field is associated with.
	 *
	 * @return int|false
	 */
	private function _getTagGroupId()
	{
		if ($this->_tagGroupId !== null) {
			return $this->_tagGroupId;
		}

		if (!preg_match('/^taggroup:(\d+)$/', $this->source, $matches)) {
			return $this->_tagGroupId = false;
		}

		return $this->_tagGroupId = (int)$matches[1];
	}
}

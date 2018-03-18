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
class Tags extends SproutBaseRelationField
{
    // Properties
    // =====================================================================

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
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Tags');
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
        return Craft::t('sprout-forms', 'Add a Tag');
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/tags/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        /** @var Element|null $element */
        if ($element !== null && $element->hasEagerLoadedElements($this->handle)) {
            $value = $element->getEagerLoadedElements($this->handle);
        }

        if ($value instanceof ElementQueryInterface) {
            $value = $value
                ->status(null)
                ->enabledForSite(false)
                ->all();
        } else if (!is_array($value)) {
            $value = [];
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
        }

        return '<p class="error">'.Craft::t('app', 'This field is not set to a valid source.').'</p>';
    }

    /**
     * @param mixed                                            $value
     * @param array|null                                       $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFormInputHtml($value, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $tags = SproutForms::$app->frontEndFields->getFrontEndTags($this->getSettings());

        $rendered = Craft::$app->getView()->renderTemplate(
            'tags/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'tags' => $tags,
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/tags.svg';
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

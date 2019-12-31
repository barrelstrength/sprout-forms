<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\base\Element;
use craft\errors\SiteNotFoundException;
use craft\fields\Tags as CraftTags;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\elements\Tag;
use craft\elements\db\ElementQueryInterface;
use craft\models\TagGroup;

use barrelstrength\sproutforms\SproutForms;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\NotSupportedException;

/**
 * Class SproutFormsTagsField
 *
 *
 * @property string $svgIconPath
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property mixed  $exampleInputHtml
 */
class Tags extends BaseRelationFormField
{
    /**
     * @var string
     */
    public $cssClasses;

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
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/tags.svg';
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
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     * @throws NotSupportedException
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        /** @var Element|null $element */
        if ($element !== null && $element->hasEagerLoadedElements($this->handle)) {
            $value = $element->getEagerLoadedElements($this->handle);
        }

        if ($value instanceof ElementQueryInterface) {
            $value = $value
                ->anyStatus()
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
                    'tagGroupId' => $tagGroup->id,
                    'targetSiteId' => $this->targetSiteId($element),
                    'sourceElementId' => $element !== null ? $element->id : null,
                    'selectionLabel' => $this->selectionLabel ? Craft::t('site', $this->selectionLabel) : static::defaultSelectionLabel(),
                ]);
        }

        return '<p class="error">'.Craft::t('sprout-forms', 'This field is not set to a valid source.').'</p>';
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/tags/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        $tags = SproutForms::$app->frontEndFields->getFrontEndTags($this->getSettings());

        $rendered = Craft::$app->getView()->renderTemplate('tags/input',
            [
                'name' => $this->handle,
                'value' => $value->ids(),
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'tags' => $tags,
            ]
        );

        return TemplateHelper::raw($rendered);
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
            return Craft::$app->getTags()->getTagGroupByUid($tagGroupId);
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

        if (!preg_match('/^taggroup:(([0-9a-f\-]+))$/', $this->source, $matches)) {
            return $this->_tagGroupId = false;
        }

        return $this->_tagGroupId = $matches[1];
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftTags::class
        ];
    }
}

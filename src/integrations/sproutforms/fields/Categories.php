<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\elements\Category;
use craft\helpers\ArrayHelper;
use craft\helpers\ElementHelper;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsCategoriesField
 *
 */
class Categories extends SproutBaseRelationField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return SproutForms::t('Categories');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Category::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return SproutForms::t('Add a category');
    }

    // Properties
    // =====================================================================

    /**
     * @var string|null The input’s boostrap class
     */
    public $boostrapClass;

    /**
     * @var int|null Branch limit
     */
    public $branchLimit;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->allowLimit = false;
        $this->allowMultipleSources = false;
        $this->settingsTemplate = 'sprout-forms/_components/fields/categories/settings';
        $this->inputTemplate = '_components/fieldtypes/Categories/input';
        $this->inputJsClass = 'Craft.CategorySelectInput';
        $this->sortable = false;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (is_array($value)) {
            /** @var Category[] $categories */
            $categories = Category::find()
                ->id($value)
                ->status(null)
                ->enabledForSite(false)
                ->all();

            // Fill in any gaps
            $categoriesService = Craft::$app->getCategories();
            $categoriesService->fillGapsInCategories($categories);

            // Enforce the branch limit
            if ($this->branchLimit) {
                $categoriesService->applyBranchLimitToCategories($categories, $this->branchLimit);
            }

            $value = ArrayHelper::getColumn($categories, 'id');
        }

        return parent::normalizeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/categories/example',
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
        // Make sure the field is set to a valid category group
        if ($this->source) {
            $source = ElementHelper::findSource(static::elementType(), $this->source, 'field');
        }

        if (empty($source)) {
            return '<p class="error">'.SproutForms::t('This field is not set to a valid category group.').'</p>';
        }

        return parent::getInputHtml($value, $element);
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

        $categories = SproutForms::$app->frontEndFields->getFrontEndCategories($settings);

        $rendered = Craft::$app->getView()->renderTemplate(
            'categories/input',
            [
                'name' => $field->handle,
                'value' => $value,
                'field' => $field,
                'settings' => $settings,
                'renderingOptions' => $renderingOptions,
                'categories' => $categories,
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    protected function inputTemplateVariables($value = null, ElementInterface $element = null): array
    {
        $variables = parent::inputTemplateVariables($value, $element);
        $variables['branchLimit'] = $this->branchLimit;

        return $variables;
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'fa fa-folder-open';
    }
}

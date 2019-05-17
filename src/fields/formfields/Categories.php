<?php

namespace barrelstrength\sproutforms\fields\formfields;

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
 *
 * @property string $svgIconPath
 * @property mixed  $exampleInputHtml
 */
class Categories extends BaseRelationFormField
{
    // Properties
    // =====================================================================

    /**
     * @var int|null Branch limit
     */
    public $branchLimit;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->allowLimit = false;
        $this->allowMultipleSources = false;
        $this->settingsTemplate = 'sprout-forms/_components/fields/formfields/categories/settings';
        $this->inputTemplate = '_components/fieldtypes/Categories/input';
        $this->inputJsClass = 'Craft.CategorySelectInput';
        $this->sortable = false;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Categories');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Category::class;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/folder-open.svg';
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add a category');
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Make sure the field is set to a valid category group
        if ($this->source) {
            $source = ElementHelper::findSource(static::elementType(), $this->source, 'field');
        }

        if (empty($source)) {
            return '<p class="error">'.Craft::t('sprout-forms', 'This field is not set to a valid category group.').'</p>';
        }

        return parent::getInputHtml($value, $element);
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/categories/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param array|null $renderingOptions
     *
     * @return \Twig\Markup
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup
    {
        $categories = SproutForms::$app->frontEndFields->getFrontEndCategories($this->getSettings());

        $rendered = Craft::$app->getView()->renderTemplate(
            'categories/input',
            [
                'name' => $this->handle,
                'value' => $value->ids(),
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'categories' => $categories,
            ]
        );

        return TemplateHelper::raw($rendered);
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
    protected function inputTemplateVariables($value = null, ElementInterface $element = null): array
    {
        $variables = parent::inputTemplateVariables($value, $element);
        $variables['branchLimit'] = $this->branchLimit;

        return $variables;
    }
}

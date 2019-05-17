<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\fields\Dropdown as CraftDropdown;
use craft\fields\PlainText as CraftPlainText;

/**
 * Class SproutFormsDropdownField
 *
 *
 * @property string $modelName
 * @property string $svgIconPath
 * @property array  $compatibleCraftFields
 * @property mixed  $exampleInputHtml
 */
class Dropdown extends BaseOptionsFormField
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return CraftDropdown::class;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Dropdown');
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/chevron-circle-down.svg';
    }

    /**
     * @inheritdoc
     */
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout-forms', 'Dropdown Options');
    }

    /**
     * Adds support for edit field in the Entries section of SproutForms (Control
     * panel html)
     *
     * @inheritdoc
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/dropdown/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param array|null $renderingOptions
     *
     * @return \Twig_Markup
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig\Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'dropdown/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFields(): array
    {
        return [
            CraftPlainText::class,
            CraftDropdown::class
        ];
    }
}

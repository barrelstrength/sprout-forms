<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutforms\rules\fieldrules\TextCondition;
use Craft;
use craft\fields\RadioButtons as CraftRadioButtons;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;

/**
 * Class SproutFormsRadioButtonsField
 *
 *
 * @property string                                                     $svgIconPath
 * @property array                                                      $compatibleCraftFields
 * @property array                                                      $compatibleCraftFieldTypes
 * @property \barrelstrength\sproutforms\rules\fieldrules\TextCondition $compatibleConditional
 * @property mixed                                                      $exampleInputHtml
 */
class MultipleChoice extends BaseOptionsFormField
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Multiple Choice');
    }

    /**
     * @return bool
     */
    public function hasMultipleLabels(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/dot-circle-o.svg';
    }

    /**
     * @inheritdoc
     */
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout-forms', 'Multiple Choice Options');
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
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $options = $this->translatedOptions();

        // If this is a new entry, look for a default option
        if ($this->isFresh($element)) {
            $value = $this->defaultValue();
        }

        return Craft::$app->getView()->renderTemplate(
            '_includes/forms/radioGroup',
            [
                'name' => $this->handle,
                'value' => $value,
                'options' => $options
            ]);
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
        return Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/formfields/multiplechoice/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @param            $value
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'multiplechoice/input',
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
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftRadioButtons::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleConditions()
    {
        $textCondition = new TextCondition(['formField' => $this]);
        return $textCondition;
    }
}

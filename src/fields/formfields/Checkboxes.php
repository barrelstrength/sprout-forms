<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

/**
 * Class SproutFormsCheckboxesField
 *
 *
 * @property string $svgIconPath
 * @property mixed  $exampleInputHtml
 */
class Checkboxes extends BaseOptionsFormField
{
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
        $this->multi = true;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Checkboxes');
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
        return '@sproutbaseicons/check-square.svg';
    }

    /**
     * @inheritdoc
     */
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout-forms', 'Checkbox Options');
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $options = $this->translatedOptions();

        // If this is a new entry, look for any default options
        if ($this->isFresh($element)) {
            $value = $this->defaultValue();
        }

        return Craft::$app->getView()->renderTemplate('_includes/forms/checkboxGroup',
            [
                'name' => $this->handle,
                'values' => $value,
                'options' => $options
            ]);
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/checkboxes/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'checkboxes/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }
}

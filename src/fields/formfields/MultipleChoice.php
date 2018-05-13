<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

/**
 * Class SproutFormsRadioButtonsField
 *
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
    public function hasMultipleLabels()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
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
     */
    public function getExampleInputHtml()
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
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
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
}

<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsRadioButtonsField
 *
 */
class RadioButtons extends SproutBaseOptionsField
{
    /**
     * @var string|null The input’s boostrap class
     */
    public $boostrapClass;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return SproutForms::t('Radio Buttons');
    }

    /**
     * @inheritdoc
     */
    protected function optionsSettingLabel(): string
    {
        return SproutForms::t('Radio Button Options');
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/radiobuttons/example',
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
        $options = $this->translatedOptions();

        // If this is a new entry, look for a default option
        if ($this->isFresh($element)) {
            $value = $this->defaultValue();
        }

        return Craft::$app->getView()->renderTemplate('_includes/forms/radioGroup',
            [
                'name' => $this->handle,
                'value' => $value,
                'options' => $options
            ]);
    }

    /**
     * @param \barrelstrength\sproutforms\contracts\FieldModel $field
     * @param mixed                                            $value
     * @param mixed                                            $settings
     * @param array|null                                       $renderingOptions
     *
     * @return string
     */
    public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $rendered = Craft::$app->getView()->renderTemplate(
            'radiobuttons/input',
            [
                'name' => $field->handle,
                'value' => $value,
                'field' => $field,
                'settings' => $settings,
                'renderingOptions' => $renderingOptions
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'fa fa-dot-circle-o';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $parentRendered = parent::getSettingsHtml();

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/radiobuttons/settings',
            [
                'field' => $this,
            ]
        );

        $customRendered = $rendered.$parentRendered;

        return $customRendered;
    }
}

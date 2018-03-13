<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

/**
 * Class SproutFormsDropdownField
 *
 */
class Dropdown extends SproutBaseOptionsField
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
        return Craft::t('sprout-forms', 'Dropdown');
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/dropdown/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * Adds support for edit field in the Entries section of SproutForms (Control
     * panel html)
     *
     * @inheritdoc
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
     * @param \barrelstrength\sproutforms\contracts\FieldModel $field
     * @param mixed                                            $value
     * @param mixed                                            $settings
     * @param array|null                                       $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $rendered = Craft::$app->getView()->renderTemplate(
            'dropdown/input',
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
     * @inheritdoc
     */
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout-forms', 'Dropdown Options');
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/chevron-circle-down.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $parentRendered = parent::getSettingsHtml();

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/dropdown/settings',
            [
                'field' => $this,
            ]
        );

        $customRendered = $rendered.$parentRendered;

        return $customRendered;
    }

}

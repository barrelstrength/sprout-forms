<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

/**
 * Class SproutFormsMultiSelectField
 *
 */
class MultiSelect extends SproutBaseOptionsField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Multi Select');
    }

    // Properties
    // =====================================================================

    /**
     * @var string|null The input’s boostrap class
     */
    public $boostrapClass;

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
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/multiselect/example',
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

        // If this is a new entry, look for any default options
        if ($this->isFresh($element)) {
            $value = $this->defaultValue();
        }

        return Craft::$app->getView()->renderTemplate('_includes/forms/multiselect',
            [
                'name' => $this->handle,
                'values' => $value,
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $rendered = Craft::$app->getView()->renderTemplate(
            'multiselect/input',
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
        return Craft::t('sprout-forms', 'Multi-select Options');
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/bars.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $parentRendered = parent::getSettingsHtml();

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/multiselect/settings',
            [
                'field' => $this,
            ]
        );

        $customRendered = $rendered.$parentRendered;

        return $customRendered;
    }
}

<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;

/**
 * Class SproutFormsMultiSelectField
 *
 */
class MultiSelect extends BaseOptionsFormField
{
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
        return Craft::t('sprout-forms', 'Multi Select');
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
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout-forms', 'Multi-select Options');
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
     * @param mixed      $value
     * @param array|null $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $rendered = Craft::$app->getView()->renderTemplate(
            'multiselect/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }
}

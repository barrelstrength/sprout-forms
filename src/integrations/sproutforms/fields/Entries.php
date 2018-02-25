<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\elements\Entry;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsEntriesField
 *
 */
class Entries extends SproutBaseRelationField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Entries');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Entry::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add an Entry');
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
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/entries/example',
            [
                'field' => $this
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

        $entries = SproutForms::$app->frontEndFields->getFrontEndEntries($settings);

        $rendered = Craft::$app->getView()->renderTemplate(
            'entries/input',
            [
                'name' => $field->handle,
                'value' => $value,
                'field' => $field,
                'settings' => $settings,
                'renderingOptions' => $renderingOptions,
                'entries' => $entries,
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $parentRendered = parent::getSettingsHtml();

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/entries/settings',
            [
                'field' => $this,
            ]
        );

        $customRendered = $rendered.$parentRendered;

        return $customRendered;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/newspaper-o.svg';
    }
}

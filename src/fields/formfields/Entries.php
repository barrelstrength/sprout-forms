<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\elements\Entry;

use barrelstrength\sproutforms\SproutForms;

/**
 * Class SproutFormsEntriesField
 *
 *
 * @property string $svgIconPath
 * @property mixed  $exampleInputHtml
 */
class Entries extends BaseRelationFormField
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
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/newspaper-o.svg';
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add an Entry');
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/entries/example',
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup
    {
        $entries = SproutForms::$app->frontEndFields->getFrontEndEntries($this->getSettings());

        $rendered = Craft::$app->getView()->renderTemplate(
            'entries/input',
            [
                'name' => $this->handle,
                'value' => $value->ids(),
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'entries' => $entries,
            ]
        );

        return TemplateHelper::raw($rendered);
    }
}

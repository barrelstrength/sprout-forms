<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use yii\db\Schema;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutbase\web\assets\sproutfields\notes\QuillAsset;

class PrivateNotes extends SproutFormsBaseField
{
    /**
     * @var string
     */
    public $notes;

    /**
     * @var bool
     */
    public $hideLabel;

    /**
     * @var string
     */
    public $output;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Private Notes');
    }

    /**
     * Define database column
     *
     * @return false
     */
    public function defineContentAttribute()
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function isPlainInput()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/sticky-note.svg';
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/privatenotes/example',
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
        return Craft::$app->getView()->renderTemplate('sprout-base/sproutfields/_fields/privatenotes/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
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
        // Only visible and updated in the Control Panel
        return '';
    }
}

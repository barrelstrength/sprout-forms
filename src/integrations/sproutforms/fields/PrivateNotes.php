<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use yii\db\Schema;
use barrelstrength\sproutforms\contracts\BaseFormField;

class PrivateNotes extends BaseFormField
{
    /**
     * @var bool
     */
    public $allowRequired = false;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Private Notes');
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_formtemplates/fields/privatenotes/example',
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
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
    {
        // Only visible and updated in the Control Panel
        return '';
    }
}

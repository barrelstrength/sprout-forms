<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\base\ElementInterface;
use yii\db\Schema;
use barrelstrength\sproutforms\base\FormField;

/**
 *
 * @property string $svgIconPath
 * @property mixed  $exampleInputHtml
 */
class PrivateNotes extends FormField
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
    public function defineContentAttribute(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function isPlainInput(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/sticky-note.svg';
    }

    /**
     * @inheritdoc
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/privatenotes/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/privatenotes/example',
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
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig\Markup
    {
        // Only visible and updated in the Control Panel
        return '';
    }
}

<?php

namespace barrelstrength\sproutforms\contracts;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;

/**
 * Class SproutFormsBaseField
 *
 * @package Craft
 */
abstract class SproutFormsBaseField extends Field
{
    /**
     * @var array
     */
    protected static $fieldVariables = [];

    /**
     * @var string
     */
    protected $originalTemplatesPath;

    /**
     * @param FieldModel $field
     * @param mixed      $value
     * @param mixed      $settings
     * @param array      $renderingOptions
     *
     * @return \Twig_Markup
     */
    abstract public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null);

    /**
     * The example HTML input field that displays in the UI when a field is dragged to the form layout editor
     *
     * @return string
     */
    abstract public function getExampleInputHtml();

    final public function beginRendering()
    {
        $this->originalTemplatesPath = Craft::$app->getView()->getTemplatesPath();

        Craft::$app->getView()->setTemplatesPath($this->getTemplatesPath());
    }

    final public function endRendering()
    {
        Craft::$app->getView()->setTemplatesPath($this->originalTemplatesPath);
    }

    final public function setValue($handle, $value)
    {
        Craft::$app->httpSession->add($handle, $value);
    }

    final public function getValue($handle, $default = null)
    {
        return craft()->httpSession->get($handle, $default);
    }

    public static function addFieldVariables(array $variables)
    {
        static::$fieldVariables = array_merge(static::$fieldVariables, $variables);
    }

    public static function getFieldVariables()
    {
        return static::$fieldVariables;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return 'fields';
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '';
    }

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getTemplatesPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_components/fields/');
    }

    /**
     * Tells Sprout Forms NOT to wrap your getInputHtml() content inside any extra HTML
     *
     * @return bool
     */
    public function isPlainInput()
    {
        return false;
    }

    /**
     * Tells Sprout Forms NOT to add a (for) attribute to your field's top leve label
     *
     * @note
     * Sprout Forms renders a label with a (for) attribute for all fields.
     * If your field has multiple labels, like radio buttons do for example,
     * it would make sense for your field no to have a (for) attribute at the top level
     * but have them at the radio field level
     */
    public function hasMultipleLabels()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        return $value;
    }
}

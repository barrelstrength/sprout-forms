<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use Craft;
use craft\base\Field;

/**
 * Class FormField
 *
 * @package Craft
 *
 * @property string $templatesPath
 * @property string $fieldInputFolder
 * @property string $namespace
 * @property string $svgIconPath
 * @property string $exampleInputHtml
 */
abstract class FormField extends Field
{
    /**
     * @var bool
     */
    public $allowRequired = true;

    /**
     * @var string
     */
    protected $originalTemplatesPath;

    /**
     * The name of your form field
     *
     * @return string
     */
    public static function displayName(): string
    {
        return '';
    }

    /**
     * The icon to display for your form field
     *
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '';
    }

    /**
     * Tells Sprout Forms NOT to wrap your getInputHtml() content inside any extra HTML
     *
     * @return bool
     */
    public function isPlainInput(): bool
    {
        return false;
    }

    /**
     * Tells Sprout Forms to use a <fieldset> instead of a <div> as your field wrapper and
     * NOT to add a for="" attribute to your field's top level label.
     *
     * @note
     * Sprout Forms renders a label with a (for) attribute for all fields.
     * If your field has multiple labels, like radio buttons do for example,
     * it would make sense for your field no to have a (for) attribute at the top level
     * but have them at the radio field level. Individual inputs can then wrap each
     * <input> field in a <label> attribute.
     * @return bool
     */
    public function hasMultipleLabels(): bool
    {
        return false;
    }

    /**
     * Display or suppress the label field and behavior
     *
     * @note
     * This is useful for fields like the Opt-In field where
     * a label may not appear above the input.
     *
     * @return bool
     */
    public function displayLabel(): bool
    {
        return true;
    }

    /**
     * Display or suppress instructions field.
     *
     * @note
     * This is useful for some field types like the Section Heading field
     * where another textarea field may be the primary to use for output.
     *
     * @return bool
     */
    public function displayInstructionsField(): bool
    {
        return true;
    }

    /**
     * The namespace to use when preparing your field's <input> name. This value
     * is also prepended to the field ID.
     *
     * @example
     * All fields default to having name attributes using the fields namespace:
     *
     * <input name="fields[fieldHandle]">
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return 'fields';
    }

    /**
     * The folder path where this field template is located. This value may be overridden by users
     * when using Form Templates.
     *
     * @return string
     */
    public function getTemplatesPath(): string
    {
        $defaultFormTemplates = new AccessibleTemplates();

        return Craft::getAlias($defaultFormTemplates->getPath().'/fields/');
    }

    /**
     * The folder name within the field path to find the input HTML file for this field. By default,
     * the folder is expected to use the Field Class short name.
     *
     * @example
     * The PlainText Field Class would look for it's respective input HTML in the `plaintext/input.html`
     * file within the folder returned by getTemplatesPath()
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getFieldInputFolder(): string
    {
        $fieldClassReflection = new \ReflectionClass($this);

        return strtolower($fieldClassReflection->getShortName());
    }

    /**
     * The example HTML input field that displays in the UI when a field is dragged to the form layout editor
     *
     * @return string
     */
    abstract public function getExampleInputHtml(): string;

    /**
     * The HTML to render when a Form is output using the displayForm, displayTab, or displayField tags
     *
     * @param mixed $value
     * @param array $renderingOptions
     *
     * @return \Twig_Markup
     */
    abstract public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup;
}

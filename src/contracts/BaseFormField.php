<?php

namespace barrelstrength\sproutforms\contracts;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;

/**
 * Class BaseFormField
 *
 * @package Craft
 */
abstract class BaseFormField extends Field
{
    /**
     * @var array
     */
    protected static $fieldVariables = [];

    /**
     * @var bool
     */
    public $allowRequired = true;

    /**
     * @var string
     */
    public $cssClasses = '';

    /**
     * @var string
     */
    protected $originalTemplatesPath;

    /**
     * Allows a user to add variables to an object that can be parsed by fields
     *
     * @example
     * {% do craft.sproutForms.addFieldVariables({ entry: entry }) %}
     * {{ craft.sproutForms.displayForm('contact') }}
     *
     * @param array $variables
     */
    public static function addFieldVariables(array $variables)
    {
        static::$fieldVariables = array_merge(static::$fieldVariables, $variables);
    }

    /**
     * @return array
     */
    public static function getFieldVariables()
    {
        return static::$fieldVariables;
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
    public function getSvgIconPath()
    {
        return '';
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
     * Tells Sprout Forms NOT to add a (for) attribute to your field's top level label
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
     * Display or suppress instructions field. Useful for some field types like Notes where
     * another textarea field may be the primary to use for output.
     *
     * @return bool
     */
    public function displayInstructionsField()
    {
        return true;
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
    public function getTemplatesPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_formtemplates/fields/');
    }

    /**
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getFieldInputFolder()
    {
        $fieldClassReflection = new \ReflectionClass($this);

        return strtolower($fieldClassReflection->getShortName());
    }

    /**
     * The example HTML input field that displays in the UI when a field is dragged to the form layout editor
     *
     * @return string
     */
    abstract public function getExampleInputHtml();

    /**
     * @param mixed $value
     * @param array $renderingOptions
     *
     * @return \Twig_Markup
     */
    abstract public function getFrontEndInputHtml($value, array $renderingOptions = null);

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        return $value;
    }

    /**
     * @return array
     */
    public function getClassesOptions()
    {
        $classesIds = [];
        $options = [
            [
                'label' => Craft::t('sprout-forms','Select...'),
                'value' => ''
            ],
            [
                'label' => "Left (left)",
                'value' => 'left'
            ],
            [
                'label' => "Right (right)",
                'value' => 'right'
            ]
        ];

        $classesIds[] = 'left';
        $classesIds[] = 'right';

        $options[] = [
            'optgroup' => Craft::t('sprout-forms','Custom CSS Classes')
        ];

        if (!in_array($this->cssClasses, $classesIds) && $this->cssClasses != '') {
            $options[] = [
                'label' => $this->cssClasses,
                'value' => $this->cssClasses
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-forms','Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}

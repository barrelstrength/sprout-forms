<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

class Name extends SproutFormsBaseField implements PreviewableFieldInterface
{
    /**
     * @var bool
     */
    public $displayMultipleFields;

    /**
     * @var bool
     */
    public $displayMiddleName;

    /**
     * @var bool
     */
    public $displayPrefix;

    /**
     * @var bool
     */
    public $displaySuffix;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms','Name');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/name/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/name/settings',
            [
                'field' => $this,
            ]);
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/user.svg';
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBase::$app->utilities->getFieldContext($this, $element);

        // Set this to false for Quick Entry Dashboard Widget
        $elementId = ($element != null) ? $element->id : false;

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-base/sproutfields/_fields/name/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'value' => $value,
                'elementId' => $elementId,
                'fieldContext' => $fieldContext,
                'placeholder' => $this->placeholder
            ]);

        return TemplateHelper::raw($rendered);
    }

    /**
     * @param \barrelstrength\sproutforms\contracts\FieldModel $field
     * @param mixed                                            $value
     * @param mixed                                            $settings
     * @param array|null                                       $renderingOptions
     *
     * @return string
     */
    public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $attributes = $field->getAttributes();
        $errorMessage = SproutBase::$app->email->getErrorMessage($attributes['name'], $settings);
//        $placeholder = (isset($settings['placeholder'])) ? $settings['placeholder'] : '';

        $rendered = Craft::$app->getView()->renderTemplate(
            'name/input',
            [
                'name' => $field->handle,
                'value' => $value,
                'field' => $field,
                'errorMessage' => $errorMessage,
                'renderingOptions' => $renderingOptions,
//                'placeholder' => $placeholder
            ]
        );
//
        $this->endRendering();
//
        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
//    public function getElementValidationRules(): array
//    {
//        $rules = parent::getElementValidationRules();
//        $rules[] = 'validateEmail';
//
//        return $rules;
//    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     *
     * @param ElementInterface $element
     *
     * @return void
     */
    public function validateEmail(ElementInterface $element)
    {
//        $value = $element->getFieldValue($this->handle);
//
//        $customPattern = $this->customPattern;
//        $checkPattern = $this->customPatternToggle;
//
//        if (!SproutBase::$app->email->validateEmailAddress($value, $customPattern, $checkPattern)) {
//            $element->addError($this->handle,
//                SproutBase::$app->email->getErrorMessage(
//                    $this->name, $this)
//            );
//        }
//
//        $uniqueEmail = $this->uniqueEmail;
//
//        if ($uniqueEmail && !SproutBase::$app->email->validateUniqueEmailAddress($value, $element, $this)) {
//            $element->addError($this->handle,
//                Craft::t('sprout-forms',$this->name.' must be a unique email.')
//            );
//        }
        return true;
    }
}

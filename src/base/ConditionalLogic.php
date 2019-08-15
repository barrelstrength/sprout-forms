<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\SavableComponent;

/**
 * Class ConditionalLogic
 */
abstract class ConditionalLogic extends SavableComponent implements ConditionalInterface
{
    const CONDITIONAL_TYPE_TEXT = 'text';
    const CONDITIONAL_TYPE_DATE = 'date';

    // Traits
    // =========================================================================
    use ConditionalTrait;

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return SproutForms::$app->forms->getFormById($this->formId);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    final public function getFormFieldsAsOptions(): array
    {
        $fields = $this->getForm()->getFields();
        $fieldOptions = [];

        foreach ($fields as $field) {
            if ($field->getCompatibleConditional()){
                $fieldOptions[] = [
                    'label' => $field->name.' ('.$field->handle.')',
                    'value' => $field->handle,
                ];
            }
        }

        return $fieldOptions;
    }

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'conditionalRules';

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function validateRules($fields): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getBehaviorActions(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConditionTypes(): array
    {
        return [
            self::CONDITIONAL_TYPE_TEXT => [
                ['value' => 'is' , 'label' => 'is'],
                ['value' => 'isNot' , 'label' => 'is not'],
                ['value' => 'contains' , 'label' => 'contains'],
                ['value' => 'doesNotContains' , 'label' => 'does not contains'],
                ['value' => 'startsWith' , 'label' => 'starts with'],
                ['value' => 'doesNotStartWith' , 'label' => 'does not start with'],
                ['value' => 'endsWith' , 'label' => 'ends with'],
                ['value' => 'doesNotEndsWith' , 'label' => 'does not ends with'],
            ],
            self::CONDITIONAL_TYPE_DATE => [
                ['value' => 'isOn' ,'label' => 'is on'],
                ['value' => 'isBefore' ,'label' => 'is before'],
                ['value' => 'isAfter' ,'label' => 'is after']
            ]
        ];
    }
}


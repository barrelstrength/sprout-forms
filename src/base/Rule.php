<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\SavableComponent;

/**
 * Class ConditionalLogic
 */
abstract class Rule extends SavableComponent implements RuleInterface
{
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
     * @param bool $checkCompatibleConditional
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    final public function getFormFieldsAsOptions($checkCompatibleConditional = false): array
    {
        $fields = $this->getForm()->getFields();
        $fieldOptions = [];

        foreach ($fields as $field) {
            $row = [
                'label' => $field->name.' ('.$field->handle.')',
                'value' => $field->handle,
            ];

            if ($checkCompatibleConditional){
                $compatibleConditional = $field->getCompatibleConditional();
                if ($compatibleConditional === '' || $compatibleConditional === null){
                    $row = [];
                }
            }

            if ($row){
                $fieldOptions[] = $row;
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
    public function getConditionRules(): array
    {
        return [];
    }
}


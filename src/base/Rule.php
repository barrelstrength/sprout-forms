<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use craft\base\SavableComponent;
use yii\base\InvalidConfigException;

/**
 * Class Rule
 *
 * @property array  $conditionRules
 * @property Form   $form
 * @property array  $behaviorActions
 * @property string $behaviorActionLabel
 */
abstract class Rule extends SavableComponent implements RuleInterface
{
    // Traits
    // =========================================================================
    use RuleTrait;

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return SproutForms::$app->forms->getFormById($this->formId);
    }

    /**
     * @param bool $checkCompatibleConditional
     *
     * @return array
     * @throws InvalidConfigException
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

            if ($checkCompatibleConditional) {
                $compatibleConditional = $field->getCompatibleConditions();
                if ($compatibleConditional === null) {
                    $row = [];
                }
            }

            if ($row) {
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

    /**
     * @inheritDoc
     */
    public function getBehaviorActionLabel(): string
    {
        return '';
    }
}


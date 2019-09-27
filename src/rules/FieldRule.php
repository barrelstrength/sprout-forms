<?php

namespace barrelstrength\sproutforms\rules;

use barrelstrength\sproutforms\base\Rule;
use barrelstrength\sproutforms\base\ConditionalLogic;
use barrelstrength\sproutforms\conditionallogictypes\fieldrules\DropdownCondition;
use barrelstrength\sproutforms\conditionallogictypes\fieldrules\TextCondition;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Add a conditional logic to show or hide a Form field
 *
 * @property array       $behaviorActions
 * @property array       $behaviorActionsAsOptions
 * @property null|string $settingsHtml
 */
class FieldRule extends Rule
{
    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Field Rule');
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/conditionallogictypes/fieldrule/settings',
            [
                'conditional' => $this
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getBehaviorActions(): array
    {
        return [
            'Show',
            'Hide'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getBehaviorActionsAsOptions(): array
    {
        $options = [];
        foreach ($this->getBehaviorActions() as $behaviorAction) {
            $options[] = [
                'label' => $behaviorAction,
                'value' => strtolower($behaviorAction)
            ];
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function getConditionRules(): array
    {
        $fields = $this->getForm()->getFields();
        $rules = [];

        foreach ($fields as $field) {
            $compatibleConditional = $field->getCompatibleConditional();
            if ($compatibleConditional !== null) {
                $rules[$field->handle]['rulesAsOptions'] = $compatibleConditional->getRulesAsOptions();
            }
        }

        return $rules;
    }

    /**
     * @inheritDoc
     */
    public function getBehaviorActionLabel(): string
    {
        $behavior = '-';

        if ($this->behaviorAction && $this->behaviorTarget) {
            $form = SproutForms::$app->forms->getFormById($this->formId);
            $field = $form->getField($this->behaviorTarget);
            if ($field !== null) {
                $behavior = ucwords($this->behaviorAction).' '.$field->name;
            }
        }

        return $behavior;
    }
}


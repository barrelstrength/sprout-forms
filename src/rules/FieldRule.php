<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\rules;

use barrelstrength\sproutforms\base\Rule;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\Field;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Add a conditional logic to show or hide a Form Field
 *
 * @property array       $behaviorActions
 * @property array       $behaviorActionsAsOptions
 * @property string      $behaviorActionLabel
 * @property array       $conditionRules
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
     * @throws Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/rules/fieldrule/settings',
            [
                'fieldRule' => $this
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
     * @throws InvalidConfigException
     */
    public function getRuleTargets(): array
    {
        $fields = $this->getForm()->getFields();
        $rules = [];

        foreach ($fields as $field) {
            $rules[$field->handle]['conditionsAsOptions'] = $field->getConditionsAsOptions();
        }

        return $rules;
    }

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function getBehaviorDescription(): string
    {
        $behavior = '-';

        if ($this->behaviorAction && $this->behaviorTarget) {
            /** @var Form $form */
            $form = SproutForms::$app->forms->getFormById($this->formId);
            /** @var Field $field */
            $field = $form->getField($this->behaviorTarget);
            if ($field !== null) {
                $behavior = ucwords($this->behaviorAction).' '.$field->name;
            }
        }

        return $behavior;
    }
}


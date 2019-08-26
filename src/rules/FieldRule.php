<?php

namespace barrelstrength\sproutforms\rules;

use barrelstrength\sproutforms\base\Rule;
use barrelstrength\sproutforms\base\ConditionalLogic;
use barrelstrength\sproutforms\conditionallogictypes\fieldrules\DropdownCondition;
use barrelstrength\sproutforms\conditionallogictypes\fieldrules\TextCondition;
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
            'show',
            'hide'
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
                'value' => $behaviorAction
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
			if ($compatibleConditional !== null){
				$rules[$field->handle]['rulesAsOptions'] = $compatibleConditional->getRulesAsOptions();
				foreach ($compatibleConditional->getRules() as $rule) {
					$rules[$field->handle]['rules'][get_class($rule)]['inputValueType'] = $rule->getInputType();
				}
			}
		}

		return $rules;
	}
}


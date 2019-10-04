<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class ContainsCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'contains';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['inputValue'], 'validateCondition', 'skipOnEmpty' => false]
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateCondition()
    {
        if (strpos($this->inputValue, $this->ruleValue) !== false) {
            return true;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
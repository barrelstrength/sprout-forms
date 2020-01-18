<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class IsGreaterThanCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is greater than';
    }

    /**
     * @return bool
     */
    public function validateCondition()
    {
        if ($this->inputValue > $this->ruleValue) {
            return true;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
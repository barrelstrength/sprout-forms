<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class IsNotCheckedCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is not checked';
    }

    /**
     * @inheritDoc
     */
    public function validateCondition()
    {
        if (!filter_var($this->inputValue, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
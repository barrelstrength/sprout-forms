<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;

/**
 *
 * @property string $label
 */
class IsCheckedCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is checked';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        if (filter_var($inputValue, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return false;
    }
}
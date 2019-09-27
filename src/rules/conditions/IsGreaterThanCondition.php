<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class IsGreaterThanCondition extends BaseCondition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is greater than';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return $inputValue > $ruleValue;
    }
}
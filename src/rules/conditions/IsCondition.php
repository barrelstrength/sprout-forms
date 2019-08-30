<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class IsCondition extends BaseCondition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return $inputValue === $ruleValue;
    }
}
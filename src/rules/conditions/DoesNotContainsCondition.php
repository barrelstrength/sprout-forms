<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class DoesNotContainsCondition extends BaseCondition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'does not contains';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        if (strpos($inputValue, $ruleValue) === false) {
            return true;
        }
        return false;
    }
}
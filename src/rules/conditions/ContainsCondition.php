<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class ContainsCondition extends BaseCondition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'contains';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        if (strpos($inputValue, $ruleValue) !== false) {
            return true;
        }
        return false;
    }
}
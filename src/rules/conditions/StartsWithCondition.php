<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

/**
 *
 * @property string $label
 */
class StartsWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'starts with';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return substr_compare($inputValue, $ruleValue, 0, strlen($ruleValue)) === 0;
    }
}
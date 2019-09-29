<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;

/**
 *
 * @property string $label
 */
class DoesNotStartWithCondition extends Condition
{
    public function getLabel(): string
    {
        return 'does not starts with';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return substr_compare($inputValue, $ruleValue, 0, strlen($ruleValue)) !== 0;
    }
}
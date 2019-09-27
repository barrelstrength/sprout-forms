<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

/**
 *
 * @property string $label
 */
class DoesNotEndsWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'does not ends with';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return substr_compare($inputValue, $ruleValue, -strlen($ruleValue)) !== 0;
    }
}
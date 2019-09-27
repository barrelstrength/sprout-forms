<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

/**
 *
 * @property string $label
 */
class EndsWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'ends with';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return substr_compare($inputValue, $ruleValue, -strlen($ruleValue)) === 0;
    }
}
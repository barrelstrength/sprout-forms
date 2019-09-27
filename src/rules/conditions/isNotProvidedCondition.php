<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

/**
 *
 * @property string $label
 */
class IsNotProvidedCondition extends BaseCondition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is not provided';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return empty($inputValue) === true;
    }
}
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

    public function getValueInputHtml($name, $value): string
    {
        $html = '<input class="text fullwidth" type="text" name="'.$name.'" value="'.$value.'">';

        return $html;
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        return substr_compare($inputValue, $ruleValue, 0, strlen($ruleValue)) !== 0;
    }
}
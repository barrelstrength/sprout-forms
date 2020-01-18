<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class EndsWithCondition extends Condition
{
    public function getLabel(): string
    {
        return 'ends with';
    }

    /**
     * @return bool|mixed
     */
    public function validateCondition()
    {
        if (substr_compare($this->inputValue, $this->ruleValue, -strlen($this->ruleValue)) === 0) {
            return true;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
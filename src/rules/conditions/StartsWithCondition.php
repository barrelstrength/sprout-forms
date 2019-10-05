<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class StartsWithCondition extends Condition
{
    public function getLabel(): string
    {
        return 'starts with';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['inputValue'], 'validateCondition', 'skipOnEmpty' => false]
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateCondition()
    {
        if (substr_compare($this->inputValue, $this->ruleValue, 0, strlen($this->ruleValue)) === 0) {
            return true;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
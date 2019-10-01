<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class DoesNotEndsWithCondition extends Condition
{
    public function getLabel(): string
    {
        return 'does not ends with';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['inputValue'], 'runValidation']
        ];
    }

    /**
     * @inheritDoc
     */
    public function runValidation()
    {
        if (substr_compare($this->inputValue, $this->ruleValue, -strlen($this->ruleValue)) === 0) {
            $this->addError('inputValue', Craft::t('sprout-forms', 'Does not validate'));
        }
    }
}
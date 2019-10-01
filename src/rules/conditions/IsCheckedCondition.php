<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class IsCheckedCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is checked';
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
        if (!filter_var($this->inputValue, FILTER_VALIDATE_BOOLEAN)) {
            $this->addError('inputValue', Craft::t('sprout-forms', 'Does not validate'));
        }
    }
}
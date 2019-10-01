<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class IsNotProvidedCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is not provided';
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
        if (!(empty($this->inputValue) === true)) {
            $this->addError('inputValue', Craft::t('sprout-forms', 'Does not validate'));
        }
    }
}
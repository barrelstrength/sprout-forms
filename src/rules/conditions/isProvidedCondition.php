<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class IsProvidedCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is provided';
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['inputValue'], 'validateCondition']
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateCondition()
    {
        if (!(empty($this->inputValue) === false)) {
            $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
        }
    }
}
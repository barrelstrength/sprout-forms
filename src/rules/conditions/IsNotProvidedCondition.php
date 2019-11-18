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
            [['inputValue'], 'validateCondition', 'skipOnEmpty' => false]
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateCondition()
    {
        if (empty($this->inputValue) === true) {
            return true;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
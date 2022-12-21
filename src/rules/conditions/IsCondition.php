<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;
use Craft;

/**
 *
 * @property string $label
 */
class IsCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'is';
    }

    public function validateCondition()
    {
        if (is_array($this->inputValue) && in_array($this->ruleValue, $this->inputValue, true)) {
            return;
        }

        if ($this->inputValue === $this->ruleValue) {
            return;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate.'));
    }
}

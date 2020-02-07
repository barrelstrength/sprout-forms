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
class StartsWithCondition extends Condition
{
    public function getLabel(): string
    {
        return 'starts with';
    }

    public function validateCondition()
    {
        if (substr_compare($this->inputValue, $this->ruleValue, 0, strlen($this->ruleValue)) === 0) {
            return;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
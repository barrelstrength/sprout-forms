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
class ContainsCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'contains';
    }

    public function validateCondition()
    {
        if (strpos($this->inputValue, $this->ruleValue) !== false) {
            return;
        }

        $this->addError('inputValue', Craft::t('sprout-forms', 'Condition does not validate'));
    }
}
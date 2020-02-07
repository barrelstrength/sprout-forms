<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 * RuleInterface defines the common interface to be implemented by Rule classes.
 * A class implementing this interface should also use [[SavableComponentTrait]] and [[RuleTrait]].
 */
interface RuleInterface extends SavableComponentInterface
{
    /**
     * Returns an array of possible behaviors for a Rule
     *
     * @return array
     */
    public function getBehaviorActions(): array;

    /**
     * Returns a human-readable description of the behavior that will be performed
     *
     * @return string
     */
    public function getBehaviorDescription(): string;
}

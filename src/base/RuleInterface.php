<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 * RuleInterface defines the common interface to be implemented by Rule classes.
 * A class implementing this interface should also use [[SavableComponentTrait]] and [[RuleTrait]].
 */
interface RuleInterface extends SavableComponentInterface
{
    /**
     * Validate if the rules are meet
     *
     * @param $fields
     *
     * @return bool
     */
    public function validateRules($fields): bool;

    /**
     * Returns an array of possible Behaviors for a Rule
     *
     * @return array
     */
    public function getBehaviorActions(): array;

    /**
     * Human readable behavior action
     *
     * @return string
     */
    public function getBehaviorActionLabel(): string;
}

<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 * ConditionalInterface defines the common interface to be implemented by conditional classes.
 * A class implementing this interface should also use [[SavableComponentTrait]] and [[ConditionalTrait]].
 */
interface ConditionalInterface extends SavableComponentInterface
{
    /**
     * Validate if the rules are meet
     *
     * @param $fields
     * @return bool
     */
    public function validateRules($fields): bool;

    /**
     * Behaviors actions
     *
     * @return array
     */
    public function getBehaviorActions(): array;
}

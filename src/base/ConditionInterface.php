<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 * ConditionInterface defines the common interface to be implemented by Condition classes.
 * A class implementing this interface should also use [[SavableComponentTrait]] and [[IntegrationTrait]].
 */
interface ConditionInterface extends SavableComponentInterface
{
    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @param      $inputValue
     * @param null $ruleValue
     *
     * @return bool
     */
    public static function runValidation($inputValue, $ruleValue = null): bool;
}

<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 * IntegrationInterface defines the common interface to be implemented by Integration classes.
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
     * @param $inputValue
     * @param null $ruleValue
     * @return bool
     */
    public static function runValidation($inputValue, $ruleValue = null): bool;

	/**
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	public function getValueInputHtml($name, $value): string;
}

<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class IsNotCheckedCondition extends BaseCondition
{
	/**
	 * @inheritDoc
	 */
	public function getLabel(): string
	{
		return 'is not checked';
	}

	/**
	 * @inheritDoc
	 */
	public static function runValidation($inputValue, $ruleValue = null): bool
	{
		return $inputValue !== 1;
	}
}
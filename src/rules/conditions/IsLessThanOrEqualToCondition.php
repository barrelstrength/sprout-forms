<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class IsLessThanOrEqualToCondition extends BaseCondition
{
	/**
	 * @inheritDoc
	 */
	public function getLabel(): string
	{
		return 'is less than or equal to';
	}

	/**
	 * @inheritDoc
	 */
	public static function runValidation($inputValue, $ruleValue = null): bool
	{
		return $inputValue <= $ruleValue;
	}
}
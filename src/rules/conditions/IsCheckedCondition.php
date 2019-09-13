<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class IsCheckedCondition extends BaseCondition
{
	/**
	 * @inheritDoc
	 */
	public function getLabel(): string
	{
		return 'is checked';
	}

	/**
	 * @inheritDoc
	 */
	public static function runValidation($inputValue, $ruleValue = null): bool
	{
		if (filter_var($inputValue, FILTER_VALIDATE_BOOLEAN)) {
			return true;
		}

		return false;
	}
}
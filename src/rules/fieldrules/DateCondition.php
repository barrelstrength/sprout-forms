<?php

namespace barrelstrength\sproutforms\rules\fieldrules;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\rules\conditions\IsCondition;

class DateCondition extends BaseCondition
{
	public function getType(): string
	{
		return 'dropdown';
	}

	public static function getRules(): array
	{
		return [
			['value' => 'isOn' ,'label' => 'is on'],
			['value' => 'isBefore' ,'label' => 'is before'],
			['value' => 'isAfter' ,'label' => 'is after']
		];
	}
}
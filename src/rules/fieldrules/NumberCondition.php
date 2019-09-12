<?php

namespace barrelstrength\sproutforms\rules\fieldrules;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\base\ConditionalType;
use barrelstrength\sproutforms\rules\conditions\IsCondition;
use barrelstrength\sproutforms\rules\conditions\IsGreaterThanCondition;
use barrelstrength\sproutforms\rules\conditions\IsLessThanCondition;

class NumberCondition extends ConditionalType
{
	public function getType(): string
	{
		return 'number';
	}

	/**
	 * @return BaseCondition[]
	 */
	public function getRules(): array
	{
		return [
			new IsCondition(['formField' => $this->formField]),
			new IsGreaterThanCondition(['formField' => $this->formField]),
			new IsLessThanCondition(['formField' => $this->formField])
		];
	}
}
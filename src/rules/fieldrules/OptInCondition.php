<?php

namespace barrelstrength\sproutforms\rules\fieldrules;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\base\ConditionalType;
use barrelstrength\sproutforms\rules\conditions\IsCheckedCondition;
use barrelstrength\sproutforms\rules\conditions\IsNotCheckedCondition;

class OptInCondition extends ConditionalType
{
	public function getType(): string
	{
		return 'opt-in';
	}

	/**
	 * @return BaseCondition[]
	 */
	public function getRules(): array
	{
		return [
			new IsCheckedCondition(['formField' => $this->formField]),
			new IsNotCheckedCondition(['formField' => $this->formField])
		];
	}
}
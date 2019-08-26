<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class StarsWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'starts with';
    }

	/**
	 * @inheritDoc
	 */
	public function getValue(): string
	{
		return self::class;
	}
}
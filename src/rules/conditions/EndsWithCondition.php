<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class EndsWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'ends with';
    }

	/**
	 * @inheritDoc
	 */
	public function getValue(): string
	{
		return self::class;
	}
}
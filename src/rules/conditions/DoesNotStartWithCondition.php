<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class DoesNotStartWithCondition extends BaseCondition
{
    public $fieldRule;

    public function getLabel(): string
    {
        return 'does not starts with';
    }

	/**
	 * @inheritDoc
	 */
	public function getValue(): string
	{
		return self::class;
	}

    public function getValueInputHtml($name)
    {
        if ($this->fieldRule instanceof BaseCondition){

        }else{

        }

        return "";
    }
}
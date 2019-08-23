<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class DoesNotContainsCondition extends BaseCondition
{
    public $fieldRule;

    public static function getLabel(): string
    {
        return 'does not contains';
    }

    public static function getValue(): string
    {
        return 'doesNotContains';
    }

    public function getValueInputHtml($name)
    {
        if ($this->fieldRule instanceof BaseCondition){

        }else{

        }

        return "";
    }
}
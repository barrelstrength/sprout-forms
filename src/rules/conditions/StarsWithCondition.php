<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class StarsWithCondition extends BaseCondition
{
    public $fieldRule;

    public static function getLabel(): string
    {
        return 'starts with';
    }

    public static function getValue(): string
    {
        return 'startsWith';
    }

    public function getValueInputHtml($name)
    {
        if ($this->fieldRule instanceof BaseCondition){

        }else{

        }

        return "";
    }
}
<?php

namespace barrelstrength\sproutforms\conditionallogictypes\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class ContainsCondition extends BaseCondition
{
    public $fieldRule;

    public static function getLabel(): string
    {
        return 'contains';
    }

    public static function getValue(): string
    {
        return 'contains';
    }

    public function getValueInputHtml($name)
    {
        if ($this->fieldRule instanceof BaseCondition){

        }else{

        }

        return "";
    }
}
<?php

namespace barrelstrength\sproutforms\conditionallogictypes\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class DoesNotEndsWithCondition extends BaseCondition
{
    public $fieldRule;

    public static function getLabel(): string
    {
        return 'does not ends with';
    }

    public static function getValue(): string
    {
        return 'doesNotEndsWith';
    }

    public function getValueInputHtml($name)
    {
        if ($this->fieldRule instanceof BaseCondition){

        }else{

        }

        return "";
    }
}
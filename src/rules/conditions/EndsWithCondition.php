<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class EndsWithCondition extends BaseCondition
{
    public $fieldRule;

    public function getLabel(): string
    {
        return 'ends with';
    }

    public function getValue(): string
    {
        return 'endsWith';
    }

    public function getValueInputHtml($name)
    {
        if ($this->fieldRule instanceof BaseCondition){

        }else{

        }

        return "";
    }
}
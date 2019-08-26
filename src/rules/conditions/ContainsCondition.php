<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class ContainsCondition extends BaseCondition
{
    public $fieldRule;

    public function getLabel(): string
    {
        return 'contains';
    }

    public function getValue(): string
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
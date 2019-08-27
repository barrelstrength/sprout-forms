<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class ContainsCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'contains';
    }
}
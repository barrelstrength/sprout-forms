<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class StartsWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'starts with';
    }
}
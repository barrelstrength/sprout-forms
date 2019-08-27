<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class DoesNotEndsWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'does not ends with';
    }
}
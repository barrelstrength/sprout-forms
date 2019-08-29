<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\fields\formfields\BaseOptionsFormField;

class IsNotCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'is not';
    }
}
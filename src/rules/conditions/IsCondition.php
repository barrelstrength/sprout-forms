<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\fields\formfields\BaseOptionsFormField;

class IsCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'is';
    }
}
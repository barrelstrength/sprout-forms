<?php

namespace barrelstrength\sproutforms\rules\fieldrules;

use barrelstrength\sproutforms\base\Condition;
use barrelstrength\sproutforms\base\ConditionalType;
use barrelstrength\sproutforms\rules\conditions\IsCheckedCondition;
use barrelstrength\sproutforms\rules\conditions\IsNotCheckedCondition;

/**
 *
 * @property array|Condition[] $rules
 * @property string            $type
 */
class OptInCondition extends ConditionalType
{
    public function getType(): string
    {
        return 'opt-in';
    }

    /**
     * @return Condition[]
     */
    public function getConditions(): array
    {
        return [
            new IsCheckedCondition(['formField' => $this->formField]),
            new IsNotCheckedCondition(['formField' => $this->formField])
        ];
    }
}
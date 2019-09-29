<?php

namespace barrelstrength\sproutforms\rules\fieldrules;

use barrelstrength\sproutforms\base\Condition;
use barrelstrength\sproutforms\base\ConditionalType;
use barrelstrength\sproutforms\rules\conditions\ContainsCondition;
use barrelstrength\sproutforms\rules\conditions\DoesNotContainsCondition;
use barrelstrength\sproutforms\rules\conditions\IsNotProvidedCondition;
use barrelstrength\sproutforms\rules\conditions\IsProvidedCondition;

/**
 *
 * @property array|Condition[] $rules
 * @property string            $type
 */
class ParagraphCondition extends ConditionalType
{
    public function getType(): string
    {
        return 'paragraph';
    }

    /**
     * @return Condition[]
     */
    public function getRules(): array
    {
        return [
            new IsProvidedCondition(['formField' => $this->formField]),
            new IsNotProvidedCondition(['formField' => $this->formField]),
            new ContainsCondition(['formField' => $this->formField]),
            new DoesNotContainsCondition(['formField' => $this->formField])
        ];
    }
}
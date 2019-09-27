<?php

namespace barrelstrength\sproutforms\rules\fieldrules;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\base\ConditionalType;
use barrelstrength\sproutforms\rules\conditions\ContainsCondition;
use barrelstrength\sproutforms\rules\conditions\DoesNotContainsCondition;
use barrelstrength\sproutforms\rules\conditions\DoesNotEndsWithCondition;
use barrelstrength\sproutforms\rules\conditions\DoesNotStartWithCondition;
use barrelstrength\sproutforms\rules\conditions\EndsWithCondition;
use barrelstrength\sproutforms\rules\conditions\IsCondition;
use barrelstrength\sproutforms\rules\conditions\IsNotCondition;
use barrelstrength\sproutforms\rules\conditions\StartsWithCondition;

/**
 *
 * @property array|\barrelstrength\sproutforms\base\BaseCondition[] $rules
 * @property string                                                 $type
 */
class TextCondition extends ConditionalType
{
    public function getType(): string
    {
        return 'text';
    }

    /**
     * @return BaseCondition[]
     */
    public function getRules(): array
    {
        return [
            new IsCondition(['formField' => $this->formField]),
            new IsNotCondition(['formField' => $this->formField]),
            new ContainsCondition(['formField' => $this->formField]),
            new DoesNotContainsCondition(['formField' => $this->formField]),
            new StartsWithCondition(['formField' => $this->formField]),
            new DoesNotStartWithCondition(['formField' => $this->formField]),
            new EndsWithCondition(['formField' => $this->formField]),
            new DoesNotEndsWithCondition(['formField' => $this->formField])
        ];
    }
}
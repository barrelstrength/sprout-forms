<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponent;

/**
 * Class ConditionalType
 *
 * @property array  $rulesAsOptions
 * @property array  $rules
 * @property string $type
 */
abstract class ConditionalType extends SavableComponent implements ConditionalTypeInterface
{
    public $formField;

    /**
     * @inheritDoc
     */
    public function getConditions(): array
    {
        return [];
    }

    public function conditions(): array
    {
        /** @var Condition[] $conditions */
        $conditions = $this->getConditions();
        $options = [];

        if ($conditions) {
            foreach ($conditions as $rule) {
                $options[] = [
                    'label' => $rule->getLabel(),
                    'value' => get_class($rule)
                ];
            }
        }

        return $options;
    }
}

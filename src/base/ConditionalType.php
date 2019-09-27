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
    public function getRules(): array
    {
        return [];
    }

    public function getRulesAsOptions(): array
    {
        /** @var BaseCondition[] $rules */
        $rules = $this->getRules();
        $options = [];

        if ($rules) {
            foreach ($rules as $rule) {
                $options[] = [
                    'label' => $rule->getLabel(),
                    'value' => get_class($rule)
                ];
            }
        }

        return $options;
    }

    public function getType(): string
    {
        return "";
    }
}

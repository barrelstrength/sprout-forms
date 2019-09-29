<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 *
 */
interface ConditionalTypeInterface extends SavableComponentInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return array
     */
    public function getConditions(): array;

    /**
     * @return array
     */
    public function getConditionsAsOptions(): array;
}

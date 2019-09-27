<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 *
 */
interface ConditionalTypeInterface extends SavableComponentInterface
{
    /**
     * @return array
     */
    public function getRules(): array;

    /**
     * @return array
     */
    public function getRulesAsOptions(): array;

    /**
     * @return string
     */
    public function getType(): string;
}

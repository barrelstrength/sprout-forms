<?php

namespace barrelstrength\sproutforms\rules;

use barrelstrength\sproutforms\base\Integration;
use Craft;
use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;

/**
 * Class MissingIntegration
 *
 * @package barrelstrength\sproutforms\integrationtypes
 */
class MissingConditional extends Integration implements MissingComponentInterface
{
    // Traits
    // =========================================================================

    use MissingComponentTrait;

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Missing Conditional');
    }
}


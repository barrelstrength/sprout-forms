<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\Integration;
use Craft;
use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;

/**
 * Class MissingIntegration
 *
 * @package barrelstrength\sproutforms\integrationtypes
 */
class MissingIntegration extends Integration implements MissingComponentInterface
{
    use MissingComponentTrait;

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Missing Integration');
    }
}


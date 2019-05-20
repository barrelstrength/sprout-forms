<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ElementIntegration;
use barrelstrength\sproutforms\base\Integration;
use Craft;
use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;
use craft\elements\Entry;
use craft\elements\User;
use craft\fields\Date;
use craft\fields\PlainText;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\web\IdentityInterface;

/**
 * Class MissingIntegration
 *
 * @package barrelstrength\sproutforms\integrationtypes
 */
class MissingIntegration extends Integration implements MissingComponentInterface
{
    // Traits
    // =========================================================================

    use MissingComponentTrait;

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Missing Integration');
    }
}


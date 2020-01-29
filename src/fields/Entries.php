<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields;

use barrelstrength\sproutforms\elements\Entry as EntryElement;
use Craft;
use craft\fields\BaseRelationField;

/**
 * Entries represents an Entries field.
 */
class Entries extends BaseRelationField
{

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Entries (Sprout Forms)');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add an entry');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return EntryElement::class;
    }
}

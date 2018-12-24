<?php

namespace barrelstrength\sproutforms\fields;

use barrelstrength\sproutforms\elements\Entry as EntryElement;
use Craft;
use craft\fields\BaseRelationField;

/**
 * Entries represents an Entries field.
 */
class Entries extends BaseRelationField
{
    // Static
    // =========================================================================

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
    protected static function elementType(): string
    {
        return EntryElement::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add an entry');
    }
}

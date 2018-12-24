<?php

namespace barrelstrength\sproutforms\fields;

use barrelstrength\sproutforms\elements\Form as FormElement;
use Craft;
use craft\fields\BaseRelationField;

/**
 * Forms represents a Forms field.
 */
class Forms extends BaseRelationField
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Forms (Sprout Forms)');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return FormElement::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add a form');
    }
}

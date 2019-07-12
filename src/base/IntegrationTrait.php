<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Entry;

/**
 * IntegrationTrait implements the common methods and properties for Integration classes.
 */
trait IntegrationTrait
{
    // Properties
    // =========================================================================

    /**
     * @var int
     */
    public $formId;

    /**
     * @var string
     */
    public $name;

    /**
     * Whether this Integration will be processed when a form is submitted
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * The Form Entry Element associated with an Integration
     *
     * @var Entry
     */
    public $entry;

    /**
     * The mapped fields
     *
     * @var array|null
     */
    public $fieldMapping;

    /**
     * Whether this Integration will be submitted
     *
     * @var boolean
     */
    public $confirmation;
}

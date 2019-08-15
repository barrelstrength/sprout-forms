<?php

namespace barrelstrength\sproutforms\base;

/**
 * ConditionalTrait implements the common methods and properties for Conditional classes.
 */
trait ConditionalTrait
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
     * Whether this Conditional will be processed when a form is displayed
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * The Conditional Rules
     *
     * @var array|null
     */
    public $conditionalRules;

    /**
     * @var string
     */
    public $behaviorAction;

    /**
     * @var string
     */
    public $behaviorTarget;
}

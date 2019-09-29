<?php

namespace barrelstrength\sproutforms\base;

/**
 * RuleTrait implements the common properties Rule classes.
 */
trait RuleTrait
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

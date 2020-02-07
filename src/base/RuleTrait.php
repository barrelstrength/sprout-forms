<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

/**
 * RuleTrait implements the common properties Rule classes.
 */
trait RuleTrait
{
    /**
     * The ID of the Form where this Rule exists
     *
     * @var int
     */
    public $formId;

    /**
     * The name given to this Rule
     *
     * @var string
     */
    public $name;

    /**
     * Whether this Rule will be processed when a form is displayed
     *
     * @var boolean
     */
    public $enabled = true;

    /**
     * The Conditional Rules
     *
     * @var array|null
     */
    public $conditions;

    /**
     * @var string
     */
    public $behaviorAction;

    /**
     * @var string
     */
    public $behaviorTarget;
}

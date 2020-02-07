<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;

/**
 * IntegrationTrait implements the common methods and properties for Integration classes.
 */
trait IntegrationTrait
{
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
     * The ID of the Form where an Integration exists
     *
     * @var int
     */
    public $formId;

    /**
     * @var Form The Form Element associated with an Integration
     */
    public $form;

    /**
     * The Form Entry Element associated with an Integration
     *
     * @var Entry
     */
    public $formEntry;

    /**
     * The Field Mapping settings
     *
     * This data is saved to the database as JSON in the settings column and populated
     * as an array when an Integration Component is created
     *
     * [
     *   [
     *     'sourceFormField' => 'title',
     *     'targetIntegrationField' => 'title'
     *   ],
     *   [
     *     'sourceFormField' => 'customFormFieldHandle',
     *     'targetIntegrationField' => 'customTargetFieldHandle'
     *   ]
     * ]
     *
     * @var array|null
     */
    public $fieldMapping;

    /**
     * Statement that gets evaluated to true/false to determine this Integration will be submitted
     *
     * @var boolean
     */
    public $sendRule;
}

<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 * IntegrationInterface defines the common interface to be implemented by Integration classes.
 * A class implementing this interface should also use [[SavableComponentTrait]] and [[IntegrationTrait]].
 */
interface IntegrationInterface extends SavableComponentInterface
{
    /**
     * Message to display when the submit() action is successful
     *
     * @return string|null
     */
    public function getSuccessMessage();

    /**
     * Prepare and send the submission to the desired endpoint
     *
     * @return bool
     */
    public function submit(): bool;

    /**
     * Returns an array of fields to be used for the dropdown of each row of the mapping.
     * Integrations will display a plain text field by default.
     *
     * @return array
     * @example
     *       return [
     *       0 => [
     *       0 => [
     *       'label' => 'Title',
     *       'value' => 'title'
     *       ],
     *       1 => [
     *       'label' => 'Slug',
     *       'value' => 'slug'
     *       ]
     *       ],
     *       1 => [
     *       0 => [
     *       'label' => 'Title',
     *       'value' => 'title'
     *       ],
     *       1 => [
     *       'label' => 'Slug',
     *       'value' => 'slug'
     *       ]
     *       ]
     *       ];
     *
     */
    public function getTargetIntegrationFieldsAsMappingOptions(): array;

    /**
     * Returns an array that represents the Target Integration field values
     *
     * The $this->fieldMapping property will be populated from the values
     * saved via the settings defined in an Integrations
     * $this->getFieldMappingSettingsHtml() method
     *
     * [
     *   'title' => 'Title of Form Entry',
     *   'customTargetFieldHandle' => 'Value of Custom Field'
     * ]
     *
     * @return array
     */
    public function getTargetIntegrationFieldValues(): array;
}

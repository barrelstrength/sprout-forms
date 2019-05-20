<?php

namespace barrelstrength\sproutforms\base;

use craft\base\SavableComponentInterface;

/**
 * IntegrationInterface defines the common interface to be implemented by Integration classes.
 * A class implementing this interface should also use [[SavableComponentTrait]] and [[IntegrationTrait]].
 */
interface IntegrationInterface extends SavableComponentInterface
{
    /**
     * Returns action URL that runs to update the targetIntegrationFieldColumns. This will be used in the
     * This action should return an array of input fields that can be used to update the target columns
     *
     * @return string|null
     */
    public function getUpdateTargetFieldsAction();

    /**
     * Process the submission and field mapping settings to prepare the payload.
     *
     * In this context, $this->fieldMapping will be populated from the values
     * saved via the settings defined in $this->getFieldMappingSettingsHtml()
     *
     * @return array
     */
    public function resolveFieldMapping(): array;

    /**
     * Send the submission to the desired endpoint
     *
     * @return bool
     */
    public function submit(): bool;
}

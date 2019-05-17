<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;
use craft\base\Model;
use Craft;
use craft\fields\Date as CraftDate;
use craft\fields\Dropdown as CraftDropdown;
use craft\fields\Number as CraftNumber;
use craft\fields\PlainText as CraftPlainText;

/**
 * Class IntegrationType
 *
 * @package Craft
 *
 * @property string $fieldMappingSettingsHtml
 * @property void   $settingsHtml
 * @property string $type
 */
abstract class Integration extends Model
{
    /**
     * The ID of the integration stored in the database
     *
     * @var int
     */
    public $integrationId;

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
     * The Form Element where the Integration is used
     *
     * @var Form
     */
    public $form;

    /**
     * @var array|null The fields mapped
     */
    public $fieldMapping;

    /**
     * The name the User gives an integration after it is created
     *
     * @var string
     */
    public $name;

    /**
     * Name of the Integration that displays as an option in the Form settings
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Send the submission to the desired endpoint
     *
     * @return bool
     */
    abstract public function submit(): bool;

    /**
     *
     * Settings that help us customize the Field Mapping Table
     *
     * Each settings template will also call a Twig Field Mapping Table Macro to help with the field mapping (can we just have a Twig Macro that wraps the default Craft Table for this and outputs two columns?)
     *
     * @return string|null
     */
    public function getSettingsHtml()
    {
        return null;
    }

    /**
     * Process the submission and field mapping settings to prepare the payload.
     *
     * In this context, $this->fieldMapping will be populated from the values
     * saved via the settings defined in $this->getFieldMappingSettingsHtml()
     *
     * @return mixed
     */
    public function resolveFieldMapping()
    {
        return $this->fieldMapping ?? [];
    }

    /**
     * Returns the HTML where a user will prepare a field mapping
     *
     * @return string|null
     */
    public function getFieldMappingSettingsHtml()
    {
        return null;
    }

    /**
     * Prepares a list of the Form Fields from the current form that a user can choose to map to an endpoint
     *
     * @param bool $addOptGroup
     *
     * @return array
     */
    public function getFormFieldsAsMappingOptions($addOptGroup = false): array
    {
        $options = [];

        if ($addOptGroup) {
            $options[] = ['optgroup' => Craft::t('sprout-forms', 'Default Fields')];
        }

        $options = array_merge($options, [
            [
                'label' => 'Form ID',
                'value' => 'id',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class,
                    CraftNumber::class
                ]
            ],
            [
                'label' => 'Title',
                'value' => 'title',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class
                ]
            ],
            [
                'label' => 'Date Created',
                'value' => 'dateCreated',
                'compatibleCraftFields' => [
                    CraftDate::class
                ]
            ],
            [
                'label' => 'IP Address',
                'value' => 'ipAddress',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class
                ]
            ],
            [
                'label' => 'User Agent',
                'value' => 'userAgent',
                'compatibleCraftFields' => [
                    CraftPlainText::class
                ]
            ]
        ]);

        $fields = $this->form->getFields();

        if (count($fields)) {
            if ($addOptGroup) {
                $options[] = [
                    'optgroup' => Craft::t('sprout-forms', 'Custom Fields')
                ];
            }

            foreach ($fields as $field) {
                $options[] = [
                    'label' => $field->name,
                    'value' => $field->handle,
                    'compatibleCraftFields' => $field->getCompatibleCraftFields(),
                    'fieldType' => get_class($field)
                ];
            }
        }

        return $options;
    }

    /**
     * @param $isValid
     * @param $message
     */
    public function logResponse($isValid, $message)
    {
        $this->entry->addEntryIntegrationLog($this->integrationId, $isValid, $message);
    }
}


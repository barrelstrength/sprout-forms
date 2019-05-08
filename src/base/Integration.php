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
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $integrationId;

    /**
     * @var Entry
     */
    public $entry;

    /**
     * @var Form
     */
    public $form;

    /**
     * The default Sprout Forms Entry does not require a mapping. All other integrations will likely leave this set to true.
     *
     * @var boolean
     */
    public $hasFieldMapping = true;

    /**
     * @var array|null The fields mapped
     */
    public $fieldsMapped;

    /**
     * @var boolean
     */
    public $enabled = true;

    /**
     * Name of the Integration
     *
     * @return mixed
     */
    abstract public function getName();

    /**
     * Return Class name as Type
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Send the submission to the desired endpoint
     *
     * @return boolean
     */
    abstract public function submit(): bool;

    /**
     * Settings that help us customize the Field Mapping Table
     *
     * Each settings template will also call a Twig Field Mapping Table Macro to help with the field mapping (can we just have a Twig Macro that wraps the default Craft Table for this and outputs two columns?)
     */
    public function getSettingsHtml()
    {
    }

    /**
     * Process the submission and field mapping settings to get the payload. Resolve the field mapping.
     *
     * @return mixed
     */
    public function resolveFieldMapping()
    {
        return $this->fieldsMapped ?? [];
    }

    /**
     * Returns a default field mapping html
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getFieldMappingSettingsHtml(): string
    {
        if (!$this->hasFieldMapping) {
            return '';
        }

        if (empty($this->fieldsMapped)) {
            // show all the form fields
            foreach ($this->getFormFieldsAsOptions() as $formField) {
                $this->fieldsMapped[] = [
                    'sproutFormField' => $formField['value'],
                    'integrationField' => ''
                ];
            }
        }

        $rendered = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'editableTableField',
            [
                [
                    'label' => Craft::t('sprout-forms', 'Field Mapping'),
                    'instructions' => Craft::t('sprout-forms', 'Define your field mapping.'),
                    'id' => 'fieldsMapped',
                    'name' => 'fieldsMapped',
                    'addRowLabel' => Craft::t('sprout-forms', 'Add a field mapping'),
                    'cols' => [
                        'sproutFormField' => [
                            'heading' => Craft::t('sprout-forms', 'Form Field'),
                            'type' => 'singleline',
                            'class' => 'code'
                        ],
                        'integrationField' => [
                            'heading' => Craft::t('sprout-forms', 'API Field'),
                            'type' => 'singleline',
                            'class' => 'code',
                            'placeholder' => Craft::t('sprout-forms', 'Leave blank and no data will be mapped')
                        ]
                    ],
                    'rows' => $this->fieldsMapped
                ]
            ]);

        return $rendered;
    }

    /**
     * @param bool $addOptGroup
     *
     * @return array
     */
    public function getFormFieldsAsOptions($addOptGroup = false): array
    {
        $fields = $this->form->getFields();
        $commonFields = [
            CraftPlainText::class,
            CraftDropdown::class
        ];
        $options = [];

        if ($addOptGroup) {
            $options[] = ['optgroup' => Craft::t('sprout-forms', 'Default')];
        }

        $options = array_merge($options, [
            [
                'label' => 'Id',
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
                'compatibleCraftFields' => $commonFields
            ],
            [
                'label' => 'Ip Address',
                'value' => 'ipAddress',
                'compatibleCraftFields' => $commonFields
            ],
            [
                'label' => 'Date Created',
                'value' => 'dateCreated',
                'compatibleCraftFields' => [
                    CraftDate::class
                ]
            ]
        ]);

        if (count($fields)) {
            if ($addOptGroup) {
                $options[] = [
                    'optgroup' => Craft::t('sprout-forms', 'Form Fields')
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


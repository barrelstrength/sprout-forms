<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\fields\formfields\Number;
use barrelstrength\sproutforms\fields\formfields\OptIn;
use barrelstrength\sproutforms\fields\formfields\SingleLine;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\SavableComponent;
use craft\fields\Date as CraftDate;
use craft\fields\Dropdown as CraftDropdown;
use craft\fields\Number as CraftNumber;
use craft\fields\PlainText as CraftPlainText;
use yii\base\InvalidConfigException;

/**
 * Class IntegrationType
 *
 * @package Craft
 *
 * @property string $fieldMappingSettingsHtml
 * @property void   $settingsHtml
 * @property array  $sourceFormFields
 * @property void   $customSourceFormFields
 * @property Form   $form
 * @property array  $sendRuleOptions
 * @property array  $targetIntegrationFields
 * @property array  $targetIntegrationFieldsAsMappingOptions
 * @property array  $targetIntegrationFieldValues
 * @property array  $indexedFieldMapping
 * @property array  $defaultSourceMappingAttributes
 * @property string $type
 */
abstract class Integration extends SavableComponent implements IntegrationInterface
{
    use IntegrationTrait;

    protected $successMessage;

    /**
     * @return array|void|null
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        /**
         * Make sure we have a formId, if not, we're just instantiating a
         *    generic element and should add it shortly. We need the Form ID
         *    to properly prepare the fieldMapping.
         */
        if ($this->formId) {
            $this->refreshFieldMapping();
        }
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        if (!$this->form) {
            $this->form = SproutForms::$app->forms->getFormById($this->formId);
        }

        return $this->form;
    }

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'fieldMapping';

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage()
    {
        if ($this->successMessage !== null) {
            return $this->successMessage;
        }

        return Craft::t('sprout-forms', 'Success');
    }

    /**
     * @inheritDoc
     */
    public function submit(): bool
    {
        return false;
    }

    /**
     * Returns a list of Source Form Fields as Field Instances
     *
     * Field Instances will be used to help create the fieldMapping using field handles.
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getSourceFormFields(): array
    {
        $sourceFormFieldsData = $this->getDefaultSourceMappingAttributes();

        $sourceFormFields = [];

        foreach ($sourceFormFieldsData as $sourceFormFieldData) {
            /** @var FormField $fieldInstance */
            $fieldInstance = new $sourceFormFieldData['type']();
            $fieldInstance->name = $sourceFormFieldData['name'];
            $fieldInstance->handle = $sourceFormFieldData['handle'];
            $fieldInstance->setCompatibleCraftFields($sourceFormFieldData['compatibleCraftFields']);
            $sourceFormFields[] = $fieldInstance;
        }

        $fields = $this->getForm()->getFields();

        if (count($fields)) {
            foreach ($fields as $field) {
                $sourceFormFields[] = $field;
            }
        }

        return $sourceFormFields;
    }

    /**
     * Prepares a list of the Form Fields from the current form that a user can choose
     * to map to an endpoint. Fields are returned in a Select dropdown compatible format.
     *
     * @param bool $addOptGroup
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getSourceFormFieldsAsMappingOptions($addOptGroup = false): array
    {
        $options = [];

        if ($addOptGroup) {
            $options[] = ['optgroup' => Craft::t('sprout-forms', 'Default Fields')];
        }

        $options = array_merge($options, [
            [
                'label' => Craft::t('sprout-forms', 'Form ID'),
                'value' => 'formId',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class,
                    CraftNumber::class
                ],
                'fieldType' => SingleLine::class
            ],
            [
                'label' => Craft::t('sprout-forms', 'Entry ID'),
                'value' => 'id',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class,
                    CraftNumber::class
                ],
                'fieldType' => SingleLine::class
            ],
            [
                'label' => Craft::t('sprout-forms', 'Title'),
                'value' => 'title',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class
                ],
                'fieldType' => SingleLine::class
            ],
            [
                'label' => Craft::t('sprout-forms', 'Date Created'),
                'value' => 'dateCreated',
                'compatibleCraftFields' => [
                    CraftDate::class
                ],
                'fieldType' => SingleLine::class
            ],
            [
                'label' => Craft::t('sprout-forms', 'IP Address'),
                'value' => 'ipAddress',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class
                ],
                'fieldType' => SingleLine::class
            ],
            [
                'label' => Craft::t('sprout-forms', 'User Agent'),
                'value' => 'userAgent',
                'compatibleCraftFields' => [
                    CraftPlainText::class
                ],
                'fieldType' => SingleLine::class
            ]
        ]);

        $fields = $this->getForm()->getFields();

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
                    'compatibleCraftFields' => $field->getCompatibleCraftFieldTypes(),
                    'fieldType' => get_class($field)
                ];
            }
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function getTargetIntegrationFieldsAsMappingOptions(): array
    {
        return [];
    }

    /**
     * Represents a Field Mapping as a one-dimensional array where the
     * key is the sourceFormFieldHandle and the value is the targetIntegrationField handle
     *
     * [
     *   'title' => 'title',
     *   'customFormFieldHandle' => 'customTargetFieldHandle'
     * ]
     *
     * @return array
     * @var array
     */
    public function getIndexedFieldMapping(): array
    {
        if ($this->fieldMapping === null) {
            return [];
        }

        $indexedFieldMapping = [];

        // Update our stored settings to use the sourceFormField handle as the key of our array
        foreach ($this->fieldMapping as $fieldMap) {
            $indexedFieldMapping[$fieldMap['sourceFormField']] = $fieldMap['targetIntegrationField'];
        }

        return $indexedFieldMapping;
    }

    /**
     * Updates the Field Mapping with any fields that have been added
     * to the Field Layout for a given form
     *
     * @throws InvalidConfigException
     */
    public function refreshFieldMapping()
    {
        $newFieldMapping = [];
        $sourceFormFields = $this->getSourceFormFields();
        $indexedFieldMapping = $this->getIndexedFieldMapping();

        // Loop through the current list of form fields and match them to any existing fieldMapping settings
        foreach ($sourceFormFields as $sourceFormField) {
            // If the handle exists in our old field mapping (a field that was just
            // added to the form may not exist yet) use that value. Default to empty string.
            $targetIntegrationField = $indexedFieldMapping[$sourceFormField->handle] ?? '';

            $newFieldMapping[] = [
                'sourceFormField' => $sourceFormField->handle,
                'targetIntegrationField' => $targetIntegrationField
            ];
        }

        $this->fieldMapping = $newFieldMapping;
    }

    /**
     * @inheritDoc
     */
    public function getTargetIntegrationFieldValues(): array
    {
        if (!$this->fieldMapping) {
            return null;
        }

        $fields = [];
        $formEntry = $this->formEntry;

        foreach ($this->fieldMapping as $fieldMap) {
            if (isset($formEntry->{$fieldMap['sourceFormField']}) && $fieldMap['targetIntegrationField']) {
                $fields[$fieldMap['targetIntegrationField']] = $formEntry->{$fieldMap['sourceFormField']};
            }
        }

        return $fields;
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
     * @return array
     * @throws InvalidConfigException
     */
    final public function getSendRuleOptions(): array
    {
        $fields = $this->getForm()->getFields();
        $optIns = [];
        $fieldHandles = [];

        foreach ($fields as $field) {
            if (get_class($field) == OptIn::class) {
                $optIns[] = [
                    'label' => $field->name.' ('.$field->handle.')',
                    'value' => $field->handle,
                ];
                $fieldHandles[] = $field->handle;
            }
        }

        $options = [
            [
                'label' => Craft::t('sprout-forms', 'Always'),
                'value' => '*'
            ]
        ];

        $options = array_merge($options, $optIns);

        $customSendRule = $this->sendRule;

        $options[] = [
            'optgroup' => Craft::t('sprout-forms', 'Custom Rule')
        ];

        if (!in_array($this->sendRule, $fieldHandles, false) && $customSendRule != '*') {
            $options[] = [
                'label' => $customSendRule,
                'value' => $customSendRule
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-forms', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }

    /**
     * @return array
     */
    protected function getDefaultSourceMappingAttributes(): array
    {
        $sourceFormFieldsData = [
            [
                'name' => Craft::t('sprout-forms', 'Form ID'),
                'handle' => 'formId',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class,
                    CraftNumber::class
                ],
                'type' => Number::class
            ],
            [
                'name' => Craft::t('sprout-forms', 'Entry ID'),
                'handle' => 'id',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class,
                    CraftNumber::class
                ],
                'type' => Number::class
            ],
            [
                'name' => Craft::t('sprout-forms', 'Title'),
                'handle' => 'title',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class
                ],
                'type' => SingleLine::class
            ],
            [
                'name' => Craft::t('sprout-forms', 'Date Created'),
                'handle' => 'dateCreated',
                'compatibleCraftFields' => [
                    CraftDate::class
                ],
                'type' => SingleLine::class
            ],
            [
                'name' => Craft::t('sprout-forms', 'IP Address'),
                'handle' => 'ipAddress',
                'compatibleCraftFields' => [
                    CraftPlainText::class,
                    CraftDropdown::class
                ],
                'type' => SingleLine::class
            ],
            [
                'name' => Craft::t('sprout-forms', 'User Agent'),
                'handle' => 'userAgent',
                'compatibleCraftFields' => [
                    CraftPlainText::class
                ],
                'type' => SingleLine::class
            ]
        ];

        return $sourceFormFieldsData;
    }
}

<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ApiIntegration;
use Craft;

/**
 * Create a Craft Entry element
 */
class ElementIntegration extends ApiIntegration
{
    public $section;

    public $entryType;

    /**
     * @var boolean
     */
    public $hasFieldMapping = true;

    public function getName() {
        return Craft::t('sprout-forms', 'Craft Entries');
    }

    /**
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml() {
        $sections = $this->getSectionsAsOptions();

        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/integrationtypes/entryelement/settings',
            [
                'integration' => $this,
                'sectionsOptions' => $sections
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function submit() {
        if ($this->section && !Craft::$app->getRequest()->getIsCpRequest()) {
            if (!$this->createEntry()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function resolveFieldMapping() {
        $fields = [];
        $entry = $this->entry;

        if ($this->fieldsMapped){
            foreach ($this->fieldsMapped as $fieldMapped) {
                if (isset($entry->{$fieldMapped['label']}) && $fieldMapped['value']){
                    $fields[$fieldMapped['value']] = $entry->{$fieldMapped['label']};
                }else{
                    // Leave default handle is the value is blank
                    if (empty($fieldMapped['value'])){
                        $fields[$fieldMapped['label']] = $entry->{$fieldMapped['label']};
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Returns a default field mapping html
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public function getFieldMappingSettingsHtml()
    {
        if (!$this->hasFieldMapping){
            return '';
        }

        if (empty($this->fieldsMapped)) {
            $this->fieldsMapped = [['label' => '', 'value' => '']];
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
                            'type' => 'select',
                            'options' => $this->getFormFieldsAsOptions()
                        ],
                        'integrationField' => [
                            'heading' => Craft::t('sprout-forms', 'Entry Field'),
                            'type' => 'select',
                            'class' => 'craftEntryFields',
                            'options' => []
                        ]
                    ],
                    'rows' => $this->fieldsMapped
                ]
            ]);

        return $rendered;
    }

    /**
     * @return bool
     */
    private function createEntry()
    {
        $entry = $this->entry;
        $fields = $this->resolveFieldMapping();

        // @todo Create the entry here

        return true;
    }

    /**
     * @return array
     */
    private function getSectionsAsOptions()
    {
        $sections = Craft::$app->getSections()->getAllSections();
        $options = [];

        foreach ($sections as $section) {
            $entryTypes = $section->getEntryTypes();

            $options[] = ['optgroup' => $section->name];

            foreach ($entryTypes as $entryType) {
                $options[] = [
                    'label' => $entryType->name,
                    'value' => $entryType->id
                ];
            }
        }

        return $options;
    }

    /**
     * Return Class name as Type
     *
     * @return string
     */
    public function getType()
    {
        return self::class;
    }
}


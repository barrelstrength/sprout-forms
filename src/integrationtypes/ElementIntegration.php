<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ApiIntegration;
use Craft;
use craft\elements\Entry;

/**
 * Create a Craft Entry element
 */
class ElementIntegration extends ApiIntegration
{
    public $entryTypeId;

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
        if ($this->entryTypeId && !Craft::$app->getRequest()->getIsCpRequest()) {
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
                if (isset($entry->{$fieldMapped['sproutFormField']}) && $fieldMapped['integrationField']){
                    $fields[$fieldMapped['integrationField']] = $entry->{$fieldMapped['sproutFormField']};
                }else{
                    // Leave default handle is the integrationField is blank
                    if (empty($fieldMapped['integrationField'])){
                        $fields[$fieldMapped['sproutFormField']] = $entry->{$fieldMapped['sproutFormField']};
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
            $this->fieldsMapped = [['sproutFormField' => '', 'integrationField' => '']];
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
     * @throws \Throwable
     */
    private function createEntry()
    {
        $entry = $this->entry;
        $fields = $this->resolveFieldMapping();
        $entryType = Craft::$app->getSections()->getEntryTypeById($this->entryTypeId);
        $entryElement = new Entry();
        $entryElement->typeId = $entryType->id;
        $entryElement->sectionId = $entryType->sectionId;

        $entryElement->setAttributes($fields, false);

        try {
            if ($entryElement->validate()){
                $result = Craft::$app->getElements()->saveElement($entryElement);
                if ($result){
                    Craft::info('Element Integration successfully saved: '.$entryElement->id, __METHOD__);
                    return true;
                }

                $entry->addError('general', Craft::t('sprout-forms', 'Unable to create Entry via Element Integration'));
            }else{
                $entry->addError('general', Craft::t('sprout-forms', 'Element Integration does not validate: '.$this->name));
            }
        } catch (\Exception $e) {
            $entry->addError('general', $e->getMessage());
        }

        return false;
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


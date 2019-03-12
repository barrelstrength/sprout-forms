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
     * @return bool
     */
    private function createEntry()
    {
        $entry = $this->entry;
        $fields = $this->resolveFieldMapping();
        $endpoint = $this->submitAction;

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
            $options[] = [
                'label' => $section->name,
                'value' => $section->id
            ];
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


<?php

namespace barrelstrength\sproutforms\integrations\sproutimport\elements;

use barrelstrength\sproutbase\app\import\base\ElementImporter;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutimport\models\jobs\SeedJob;

use Craft;

class Entry extends ElementImporter
{
    /**
     * @inheritdoc
     */
    public function getModelName()
    {
        return EntryElement::class;
    }

    /**
     * @inheritdoc
     */
    public function hasSeedGenerator()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        return SproutForms::$app->entries->saveEntry($this->model);
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayoutId($model)
    {
        /**
         * @var $model EntryElement
         */
        return $model->fieldLayoutId;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(SeedJob $seedJob)
    {
        $forms = SproutForms::$app->forms->getAllForms();

        $formOptions[''] = Craft::t('sprout-forms', 'Select a form...');

        if ($forms !== null) {
            foreach ($forms as $form) {
                $formOptions[$form->id] = $form->name;
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout-forms/_integrations/sproutimport/importers/elements/seed-generators/Entry/settings', [
            'id' => $this->getModelName(),
            'formOptions' => $formOptions
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getMockData($quantity, $settings)
    {
        $saveIds = [];
        $formId = $settings['formId'];

        $form = SproutForms::$app->forms->getFormById($formId);

        if (!empty($quantity)) {
            for ($i = 1; $i <= $quantity; $i++) {
                $fakerDate = $this->fakerService->dateTimeThisYear('now');

                $formEntry = new EntryElement();
                $formEntry->formId = $form->id;
                $formEntry->ipAddress = '127.0.0.1';
                $formEntry->userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
                $formEntry->dateCreated = date('Y-m-d H:i:s', $fakerDate->getTimestamp());
                $formEntry->dateUpdated = date('Y-m-d H:i:s', $fakerDate->getTimestamp());

                $fieldTypes = $form->getFields();

                $fields = $this->getFieldsWithMockData($fieldTypes);

                $formEntry->setContentFromPost($fields);

                SproutForms::$app->entries->saveEntry($formEntry);

                // Avoid duplication of saveIds
                if (!in_array($formEntry->id, $saveIds) && $formEntry->id !== false) {
                    $saveIds[] = $formEntry->id;
                }
            }
        }

        return $saveIds;
    }

    public function getFieldsWithMockData($fields)
    {
        $fieldsWithMockData = [];

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $fieldHandle = $field->handle;
                $fieldType = $field->type;
                $fieldImporterClass = SproutBase::$app->importers->getFieldImporterClassByType($fieldType);

                if ($fieldImporterClass != null) {
                    $fieldImporterClass->setModel($field);

                    $fieldsWithMockData[$fieldHandle] = $fieldImporterClass->getMockData();
                }
            }
        }

        return $fieldsWithMockData;
    }

    /**
     * @return array
     */
    public function getAllFieldHandles()
    {
        $fields = $this->model->getFields();

        $handles = [];
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $handles[] = $field->handle;
            }
        }

        return $handles;
    }
}
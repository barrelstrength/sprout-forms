<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrations\sproutimport\elements;

use barrelstrength\sproutbaseimport\base\ElementImporter;
use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\models\jobs\SeedJob;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\elements\Entry as FormElement;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

class Entry extends ElementImporter
{
    /**
     * @inheritdoc
     */
    public function getModelName(): string
    {
        return EntryElement::class;
    }

    /**
     * @inheritdoc
     */
    public function hasSeedGenerator(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(): bool
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
     *
     * @param SeedJob $seedJob
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSeedSettingsHtml(SeedJob $seedJob): string
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
    public function getSeedSettingsErrors($settings)
    {
        if (isset($settings['formId']) && empty($settings['formId'])) {
            return Craft::t('sprout-forms', 'Form is required.');
        }

        return null;
    }

    /**
     * @inheritdoc
     *
     * @throws Throwable
     * @throws Exception
     */
    public function getMockData($quantity, $settings)
    {
        $saveIds = [];
        $formId = $settings['formId'];

        /** @var FormElement $form */
        $form = SproutForms::$app->forms->getFormById($formId);

        if (!empty($quantity)) {
            for ($i = 1; $i <= $quantity; $i++) {
                $fakerDate = $this->fakerService->dateTimeThisYear();

                /** @var EntryElement $formEntry */
                $formEntry = new EntryElement();
                $formEntry->formId = $form->id;
                $formEntry->ipAddress = '127.0.0.1';
                $formEntry->userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36';
                $formEntry->dateCreated = date('Y-m-d H:i:s', $fakerDate->getTimestamp());
                $formEntry->dateUpdated = date('Y-m-d H:i:s', $fakerDate->getTimestamp());

                // @todo - Need to refactor for C3
                // $fieldTypes = $form->getFields();
                // $fields = $this->getFieldsWithMockData($fieldTypes);
                // $formEntry->setContentFromPost($fields);

                SproutForms::$app->entries->saveEntry($formEntry);

                // Avoid duplication of saveIds
                if (!in_array($formEntry->id, $saveIds, true) && $formEntry->id !== false) {
                    $saveIds[] = $formEntry->id;
                }
            }
        }

        return $saveIds;
    }

    public function getFieldsWithMockData($fields): array
    {
        $fieldsWithMockData = [];

        if (!empty($fields)) {

            foreach ($fields as $field) {

                $fieldHandle = $field->handle;

                $fieldType = get_class($field);

                /** @var FieldImporter $fieldImporterClass */
                $fieldImporterClass = SproutBaseImport::$app->importers->getFieldImporterClassByType($fieldType);

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
    public function getAllFieldHandles(): array
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
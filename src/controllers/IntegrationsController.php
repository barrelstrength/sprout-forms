<?php

namespace barrelstrength\sproutforms\controllers;


use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\elements\Form;
use Craft;

use craft\web\Controller as BaseController;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
use craft\base\Field;

use barrelstrength\sproutforms\SproutForms;

class IntegrationsController extends BaseController
{
    /**
     * This action allows to load the modal field template.
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionModalIntegration()
    {
        $this->requireAcceptsJson();
        $formId = Craft::$app->getRequest()->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        return $this->asJson(SproutForms::$app->integrations->getModalIntegrationTemplate($form));
    }

    /**
     * This action allows create a default integration given a type.
     *
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionCreateIntegration()
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if ($type && $form) {
            $integration = SproutForms::$app->integrations->createIntegration($type, $form);

            if ($integration) {
                return $this->returnJson(true, $integration, $form);
            }
        }

        return $this->returnJson(false, null, $form);
    }

    /**
     * Save a field.
     *
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveIntegration()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        // Get the Form these fields are related to
        $formId = $request->getRequiredBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        $type = $request->getRequiredBodyParam('type');
        $fieldId = $request->getBodyParam('fieldId');


        // required field validation
        $fieldLayout = $form->getFieldLayout();
        $fieldLayoutField = FieldLayoutFieldRecord::findOne([
                'layoutId' => $fieldLayout->id,
                'tabId' => $tabId,
                'fieldId' => $fieldId
            ]
        );

        if ($fieldLayoutField) {
            $required = $request->getBodyParam('required');
            $fieldLayoutField->required = $required !== "" ? true : false;
            $fieldLayoutField->save(false);
            $field->required = $fieldLayoutField->required;
        }

        // Set our field context
        Craft::$app->content->fieldContext = $form->getFieldContext();
        Craft::$app->content->contentTable = $form->getContentTable();

        // Save a new field
        if (!$field->id) {
            $isNewField = true;
        } else {
            $isNewField = false;
            $oldHandle = Craft::$app->fields->getFieldById($field->id)->handle;
        }

        // Save our field
        if (!$fieldsService->saveField($field)) {
            // Does not validate
            SproutForms::error('Field does not validate.');

            $variables['tabId'] = $tabId;
            $variables['field'] = $field;

            return $this->returnJson(false, $field, $form, null, $tabId);
        }

        // Check if the handle is updated to also update the titleFormat
        if (!$isNewField) {
            // Let's update the title format
            if ($oldHandle != $field->handle && strpos($form->titleFormat, $oldHandle) !== false) {
                $newTitleFormat = SproutForms::$app->forms->updateTitleFormat($oldHandle, $field->handle, $form->titleFormat);
                $form->titleFormat = $newTitleFormat;
            }
        }

        // Now let's add this field to our field layout
        // ------------------------------------------------------------

        // Set the field layout
        $oldFieldLayout = $form->getFieldLayout();
        $oldTabs = $oldFieldLayout->getTabs();
        $tabName = null;
        $response = false;

        if ($oldTabs) {
            $tabName = FieldLayoutTabRecord::findOne($tabId)->name;

            if ($isNewField) {
                $response = SproutForms::$app->fields->addFieldToLayout($field, $form, $tabId);
            } else {
                $response = SproutForms::$app->fields->updateFieldToLayout($field, $form, $tabId);
            }
        }

        // Hand the field off to be saved in the
        // field layout of our Form Element
        if ($response) {
            SproutForms::info('Field Saved');

            return $this->returnJson(true, $field, $form, $tabName, $tabId);
        } else {
            $variables['tabId'] = $tabId;
            $variables['field'] = $field;
            SproutForms::error("Couldn't save field.");
            Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'Couldn’t save field.'));

            return $this->returnJson(false, $field, $form);
        }
    }

    /**
     * Edits an existing integration.
     *
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEditIntegration()
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $id = $request->getBodyParam('integrationId');
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        $integration = IntegrationRecord::findOne($id);

        if (is_null($integration)) {
            $message = Craft::t('sprout-forms', 'The integration requested to edit no longer exists.');
            SproutForms::error($message);

            return $this->asJson([
                'success' => false,
                'error' => $message,
            ]);
        }

        return $this->asJson([
            'success' => true,
            'errors' => $integration->getErrors(),
            'integration' => [
                'id' => $integration->id,
                'name' => $integration->name
            ],
            'template' => SproutForms::$app->integrations->getModalIntegrationTemplate($form, $integration),
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteIntegration()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $fieldId = Craft::$app->request->getRequiredBodyParam('fieldId');
        $formId = Craft::$app->request->getRequiredBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById((int)$formId);

        // Backup our field context and content table
        $oldFieldContext = Craft::$app->getContent()->fieldContext;
        $oldContentTable = Craft::$app->getContent()->contentTable;

        // Set our field content and content table to work with our form output
        Craft::$app->getContent()->fieldContext = $form->getFieldContext();
        Craft::$app->getContent()->contentTable = $form->getContentTable();

        $response = Craft::$app->fields->deleteFieldById($fieldId);

        // Reset our field context and content table to what they were previously
        Craft::$app->getContent()->fieldContext = $oldFieldContext;
        Craft::$app->getContent()->contentTable = $oldContentTable;


        if ($response) {
            return $this->asJson([
                'success' => true
            ]);
        } else {
            return $this->asJson([
                'success' => false
            ]);
        }
    }

    /**
     * @param bool $success
     * @param      $integrationRecord IntegrationRecord
     * @param Form $form
     *
     * @return \yii\web\Response
     */
    private function returnJson(bool $success, $integrationRecord, Form $form)
    {
        return $this->asJson([
            'success' => $success,
            'errors' => $integrationRecord ? $integrationRecord->getErrors() : null,
            'integration' => [
                'id' => $integrationRecord->id,
                'name' => $integrationRecord->name ?? null
            ],
            //'template' => $success ? false : SproutForms::$app->integrations->getModalIntegrationTemplate($form, $integration),
        ]);
    }
}
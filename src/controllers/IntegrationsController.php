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
     * Save an Integration
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveIntegration()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $formId = $request->getRequiredBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        $type = $request->getRequiredBodyParam('type');
        $integrationId = $request->getBodyParam('integrationId');
        $enabled = $request->getBodyParam('enabled');
        $name = $request->getBodyParam('name');
        $settings = $request->getBodyParam('types.'.$type);
        $integration = SproutForms::$app->integrations->getFormIntegrationById($integrationId);

        $integration->enabled = $enabled;
        $integration->settings = json_encode($settings);
        $integration->name = $name ?? $integration->name;
        $result = $integration->save();

        if (!$result) {
            SproutForms::error('Integration does not validate.');
        }

        SproutForms::info('Integration Saved');

        return $this->returnJson($result, $integration, $form);
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

        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');
        $integration = IntegrationRecord::findOne($integrationId);

        $response = $integration->delete();

        return $this->asJson([
            'success' => $response
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetEntryFields()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryTypeId = Craft::$app->request->getRequiredBodyParam('entryTypeId');
        $integrationId = Craft::$app->request->getBodyParam('integrationId');
        $position = Craft::$app->request->getBodyParam('position');
        $sectionId = Craft::$app->request->getBodyParam('sectionId');
        $entryType = Craft::$app->getSections()->getEntryTypeById($entryTypeId);

        $fields = $entryType->getFields();
        $fieldOptions = $this->getFieldsAsOptions($fields, $integrationId, $position, $sectionId);

        return $this->asJson([
            'success' => 'true',
            'fieldOptions' => $fieldOptions
        ]);
    }

    /**
     * @param Field[] $fields
     * @return array
     */
    private function getFieldsAsOptions($fields, $integrationId = null, $position = null, $sectionId = null)
    {
        $options = [];

        $options[] = [
            'label' => Craft::t('sprout-forms', 'Select a Field'),
            'value' => ''
        ];

        $fieldsMapped = [];
        $integrationSectionId = null;

        if (!is_null($integrationId) && !is_null($sectionId)){
            $integrationRecord = IntegrationRecord::findOne($integrationId);
            $integration = $integrationRecord->getIntegrationApi();
            $fieldsMapped = $integration->fieldsMapped;
            $settings = json_decode($integrationRecord->settings, true);
            $integrationSectionId = $settings['section'] ?? null;
        }

        foreach ($fields as $field) {
            $option =  [
                'label' => $field->name.': '.$field->handle,
                'value' => $field->id
            ];

            if (!is_null($position)){
                if ($integrationSectionId == $sectionId){
                    if (isset($fieldsMapped[$position])){
                        if ($field->id == $fieldsMapped[$position]['integrationField']){
                            $option['selected'] = true;
                        }
                    }
                }
            }
            $options[] = $option;
        }

        return $options;
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
        // @todo how we should return errors to the edit integration modal? template response is disabled for now
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
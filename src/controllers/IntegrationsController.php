<?php

namespace barrelstrength\sproutforms\controllers;


use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\integrationtypes\EntryElementIntegration;
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
        $addErrorOnSubmit = $request->getBodyParam('addErrorOnSubmit');
        $name = $request->getBodyParam('name');
        $settings = $request->getBodyParam('types.'.$type);
        $integration = SproutForms::$app->integrations->getFormIntegrationById($integrationId);

        $integration->enabled = $enabled;
        $integration->settings = json_encode($settings);
        $integration->name = $name ?? $integration->name;
        $integration->addErrorOnSubmit = $addErrorOnSubmit;
        $result = $integration->save();

        if (!$result) {
            Craft::error('Integration does not validate.', __METHOD__);
        }

        Craft::info('Integration Saved', __METHOD__);

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
            Craft::error($message, __METHOD__);

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
        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');

        $fieldOptionsByRow = $this->getFieldsAsOptionsByRow($entryTypeId, $integrationId);

        return $this->asJson([
            'success' => 'true',
            'fieldOptionsByRow' => $fieldOptionsByRow
        ]);
    }

    /**
     * @param $entryTypeId
     * @param null $integrationId
     * @return array
     */
    private function getFieldsAsOptionsByRow($entryTypeId, $integrationId)
    {
        $entryType = Craft::$app->getSections()->getEntryTypeById($entryTypeId);
        $entryFields = $entryType->getFields();

        $integrationRecord = IntegrationRecord::findOne($integrationId);
        /** @var EntryElementIntegration $integration */
        $integration = $integrationRecord->getIntegrationApi();
        $fieldsMapped = $integration->fieldsMapped;
        $integrationSectionId = $integration->entryTypeId ?? null;

        $defaultEntryFields = $integration->getDefaultEntryFieldsAsOptions();
        $options = $defaultEntryFields;
        $formFields = $integration->getFormFieldsAsOptions();

        $rowPosition = 0;

        $finalOptions = [];


        foreach ($formFields as $formField) {
            $optionsByRow = $this->getCompatibleFields($options, $entryFields, $formField);
            // We have rows stored and are for the same sectionType
            if ($fieldsMapped && ($integrationSectionId == $entryTypeId)){
                if (isset($fieldsMapped[$rowPosition])){
                    foreach ($optionsByRow as $key => $option) {
                        if ($option['value'] == $fieldsMapped[$rowPosition]['integrationField'] &&
                            $fieldsMapped[$rowPosition]['sproutFormField'] == $formField['value']){
                            $optionsByRow[$key]['selected'] = true;
                        }
                    }
                }
            }

            $finalOptions[$rowPosition] = $optionsByRow;

            $rowPosition++;
        }

        return $finalOptions;
    }

    /**
     * @param array $options
     * @param array $entryFields
     * @param array $formField
     * @return array
     */
    private function getCompatibleFields(array $options, array $entryFields, array $formField)
    {
        $compatibleFields = $formField['compatibleCraftFields'] ?? '*';
        $finalOptions = [];
        // Check first default entry attributes
        foreach ($options as $option){
            if (isset($option['class'])){
                if (!in_array($option['class'], $compatibleFields)){
                    $option = null;
                }
            }

            if ($option){
                $finalOptions[] = $option;
            }
        }

        foreach ($entryFields as $field) {
            $option =  [
                'label' => $field->name.': '.$field->handle,
                'value' => $field->handle
            ];

            if (is_array($compatibleFields)){
                if (!in_array(get_class($field), $compatibleFields)){
                    $option = null;
                }
            }

            if ($option){
                $finalOptions[] = $option;
            }
        }

        return $finalOptions;
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
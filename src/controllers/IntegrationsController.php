<?php

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\integrationtypes\EntryElementIntegration;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\elements\Form;
use Craft;

use craft\web\Controller as BaseController;
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
     *
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
    public function actionGetFormFields()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryTypeId = Craft::$app->request->getRequiredBodyParam('entryTypeId');
        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');

        $fieldOptionsByRow = $this->getFieldsAsOptionsByRow($entryTypeId, $integrationId);
        return $this->asJson([
            'success' => 'true',
            'fieldOptionsByRow' => json_encode($fieldOptionsByRow)
        ]);
    }

    /**
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGetElementEntryFields()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryTypeId = Craft::$app->request->getRequiredBodyParam('entryTypeId');
        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');
        $integrationRecord = IntegrationRecord::findOne($integrationId);
        /** @var EntryElementIntegration $integration */
        $integration = $integrationRecord->getIntegrationApi();

        $entryFields = $integration->getElementFieldsAsOptions($entryTypeId);

        return $this->asJson([
            'success' => 'true',
            'fieldOptionsByRow' => $entryFields
        ]);
    }

    /**
     * @param      $entryTypeId
     * @param null $integrationId
     *
     * @return array
     */
    private function getFieldsAsOptionsByRow($entryTypeId, $integrationId)
    {
        $integrationRecord = IntegrationRecord::findOne($integrationId);
        /** @var EntryElementIntegration $integration */
        $integration = $integrationRecord->getIntegrationApi();
        $entryFields = $integration->getElementFieldsAsOptions($entryTypeId);
        $fieldsMapped = $integration->fieldsMapped;
        $integrationSectionId = $integration->entryTypeId ?? null;
        $firstRow = [
            'label' => 'None',
            'value' => ''
        ];
        $formFields = $integration->getFormFieldsAsOptions(true);
        array_unshift($formFields, $firstRow);

        $rowPosition = 0;

        $finalOptions = [];

        foreach ($entryFields as $entryField) {
            $optionsByRow = $this->getCompatibleFields($formFields, $entryField);
            // We have rows stored and are for the same sectionType
            if ($fieldsMapped && ($integrationSectionId == $entryTypeId)) {
                if (isset($fieldsMapped[$rowPosition])) {
                    foreach ($optionsByRow as $key => $option) {
                        if (isset($option['optgroup'])) {
                            continue;
                        }
                        $integrationValue = $entryField['value'] ?? $entryField->handle;

                        if ($option['value'] == $fieldsMapped[$rowPosition]['sproutFormField'] &&
                            $fieldsMapped[$rowPosition]['integrationField'] == $integrationValue) {
                            $optionsByRow[$key]['selected'] = true;
                        }
                    }
                }
            }

            $finalOptions[$rowPosition] = $optionsByRow;

            $rowPosition++;
        }
        // Removes optgroups with not fields

        $auxOptions = $finalOptions;

        foreach ($auxOptions as $rowPos => $finalOptionsByRow) {
            foreach ($finalOptionsByRow as $row => $finalOptionByRow) {

                if (isset($finalOptionByRow['optgroup'])) {
                    $removeOptGroup = true;

                    if (isset($finalOptionsByRow[$row + 1])) {
                        if (isset($finalOptionsByRow[$row + 1]['value'])) {
                            $removeOptGroup = false;
                        }
                    }
                    if ($removeOptGroup) {
                        unset($finalOptions[$rowPos][$row]);
                    }
                }
            }
        }

        return $finalOptions;
    }

    /**
     * @param array $formFields
     * @param       $entryField
     *
     * @return array
     */
    private function getCompatibleFields(array $formFields, $entryField)
    {
        $finalOptions = [];
        $groupFields = [];

        foreach ($formFields as $pos => $field) {
            if (isset($field['optgroup'])) {
                $finalOptions[] = $field;
                continue;
            }
            $compatibleFields = $field['compatibleCraftFields'] ?? '*';
            // Check default attributes
            if (isset($entryField['class'])) {
                if (is_array($compatibleFields)) {
                    if (!in_array($entryField['class'], $compatibleFields)) {
                        $field = null;
                    }
                }

                if ($field) {
                    $groupFields[] = $field;
                    $finalOptions[] = $field;
                }
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
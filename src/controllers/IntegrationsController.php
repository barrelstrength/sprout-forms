<?php

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\base\ElementIntegration;
use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\base\IntegrationInterface;
use barrelstrength\sproutforms\integrationtypes\EntryElementIntegration;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use Craft;

use craft\base\WidgetInterface;
use craft\errors\MissingComponentException;
use craft\helpers\Component as ComponentHelper;
use craft\web\Controller as BaseController;
use barrelstrength\sproutforms\SproutForms;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class IntegrationsController extends BaseController
{
    /**
     * Enable or disable an Integration
     *
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionEnableIntegration(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $integrationId = $request->getBodyParam('integrationId');
        $enabled = $request->getBodyParam('enabled');
        $enabled = $enabled == 1;
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if ($integrationId == 'saveData' && $form) {
            $form->saveData = $enabled;

            if (SproutForms::$app->forms->saveForm($form)) {
                return $this->asJson([
                    'success' => true
                ]);
            }
        }

        $pieces = explode('-', $integrationId);

        if (count($pieces) == 3) {
            $integration = SproutForms::$app->integrations->getIntegrationById($pieces[2]);
            if ($integration) {
                $integration->enabled = $enabled;
                if (SproutForms::$app->integrations->saveIntegration($integration)) {
                    return $this->returnJson(true, $integration);
                }
            }
        }

        return $this->asJson([
            'success' => false
        ]);
    }

    /**
     * Save an Integration
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function actionSaveIntegration(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $type = $request->getRequiredBodyParam('type');

        /** @var Integration $integration */
        $integration = new $type();

        $integration->id = $request->getBodyParam('integrationId');
        $integration->formId = $request->getBodyParam('formId');
        $integration->name = $request->getBodyParam('name');
        $integration->enabled = $request->getBodyParam('enabled');

        $settings = $request->getBodyParam('settings.'.$type);

        $integration = SproutForms::$app->integrations->createIntegration([
            'id' => $integration->id,
            'formId' => $integration->formId,
            'name' => $integration->name,
            'enabled' => $integration->enabled,
            'type' => get_class($integration),
            'settings' => $settings,
        ]);

        $integration = new $type($integration);

        if (!SproutForms::$app->integrations->saveIntegration($integration)) {
            Craft::error('Unable to save integration.', __METHOD__);
            return $this->returnJson(false, null);
        }

        Craft::info('Integration Saved', __METHOD__);

        return $this->returnJson(true, $integration);
    }

    /**
     * Edits an existing integration.
     *
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws BadRequestHttpException
     */
    public function actionEditIntegration(): Response
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $integrationId = $request->getBodyParam('integrationId');

        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);
        $integration->formId = $request->getBodyParam('formId');

        if ($integration === null) {
            $message = Craft::t('sprout-forms', 'No integration found with id: {id}', [
                'id' => $integrationId
            ]);
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
            'template' => SproutForms::$app->integrations->getModalIntegrationTemplate($integration),
        ]);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteIntegration(): Response
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

    public function actionGetEntryElementFields(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

//        $entryTypeId = Craft::$app->request->getRequiredBodyParam('entryTypeId');
        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');

        $fieldOptionsByRow = $this->getEntryFieldsAsOptionsByRow($integrationId);

        return $this->asJson([
            'success' => true,
            'fieldOptionsByRow' => $fieldOptionsByRow
        ]);
    }

    private function getEntryFieldsAsOptionsByRow($integrationId): array
    {
        /** @var EntryElementIntegration $integration */
        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);

        $firstRow = [
            'name' => 'None',
            'handle' => ''
        ];
        $sourceFormFields = $integration->getSourceFormFields();
        array_unshift($sourceFormFields, $firstRow);

        $targetElementFields = $integration->getElementCustomFieldsAsOptions($integration->entryTypeId);

        $fieldMapping = $integration->fieldMapping;
        $integrationSectionId = $integration->entryTypeId ?? null;

        $rowPosition = 0;

        $targetElementFieldOptions = [];
        foreach ($sourceFormFields as $sourceFormField) {
            $dropdownOptions = SproutForms::$app->integrations->getCompatibleTargetFields($sourceFormField, $targetElementFields);
            $targetElementFieldOptions[$rowPosition] = $dropdownOptions;
            $rowPosition++;
        }

        return $targetElementFieldOptions;

//        $rowPosition = 0;
//
//        $allTargetElementFieldOptions = [];
//
//        foreach ($targetElementFields as $targetElementField) {
//            $compatibleFields = $this->getCompatibleFields($sourceFormFields, $targetElementField);
//            $integrationValue = $targetElementField['value'] ?? $targetElementField->handle;
//            // We have rows stored and are for the same sectionType
//            if ($fieldMapping && ($integrationSectionId == $entryTypeId) &&
//                isset($fieldMapping[$rowPosition])) {
//
//                foreach ($compatibleFields as $key => $option) {
//                    if (isset($option['optgroup'])) {
//                        continue;
//                    }
//
//                    if ($option['value'] == $fieldMapping[$rowPosition]['sourceFormField'] &&
//                        $fieldMapping[$rowPosition]['targetIntegrationField'] == $integrationValue) {
//                        $compatibleFields[$key]['selected'] = true;
//                    }
//                }
//            }
//
//            $allTargetElementFieldOptions[$rowPosition] = $compatibleFields;
//
//            $rowPosition++;
//        }

//        $allTargetElementFieldOptions = $this->removeUnnecessaryOptgroups($allTargetElementFieldOptions);

//        return $allTargetElementFieldOptions;
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionGetFormFields(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryTypeId = Craft::$app->request->getRequiredBodyParam('entryTypeId');
        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');

        $fieldOptionsByRow = $this->getFieldsAsOptionsByRow($entryTypeId, $integrationId);

        return $this->asJson([
            'success' => true,
            'fieldOptionsByRow' => $fieldOptionsByRow
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionGetElementEntryFields(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');

        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);

        $entryFields = $integration->getElementCustomFieldsAsOptions($integration->entryTypeId);

        return $this->asJson([
            'success' => true,
            'fieldOptionsByRow' => $entryFields
        ]);
    }

    /**
     * @param $entryTypeId
     * @param $integrationId
     *
     * @return array
     * @throws InvalidConfigException
     */
    private function getFieldsAsOptionsByRow($entryTypeId, $integrationId): array
    {
        /** @var ElementIntegration $integration */
        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);
        $targetElementFields = $integration->getElementCustomFieldsAsOptions($entryTypeId);
        $fieldMapping = $integration->fieldMapping;
        $integrationSectionId = $integration->entryTypeId ?? null;
        $firstRow = [
            'name' => 'None',
            'handle' => ''
        ];
        $sourceFormFields = $integration->getFormFieldsAsMappingOptions(true);
        array_unshift($sourceFormFields, $firstRow);

        $rowPosition = 0;

        $allTargetElementFieldOptions = [];

        foreach ($targetElementFields as $targetElementField) {
            $compatibleFields = $this->getCompatibleFields($sourceFormFields, $targetElementField);
            $integrationValue = $targetElementField['value'] ?? $targetElementField->handle;
            // We have rows stored and are for the same sectionType
            if ($fieldMapping && ($integrationSectionId == $entryTypeId) &&
                isset($fieldMapping[$rowPosition])) {

                foreach ($compatibleFields as $key => $option) {
                    if (isset($option['optgroup'])) {
                        continue;
                    }

                    if ($option['value'] == $fieldMapping[$rowPosition]['sourceFormField'] &&
                        $fieldMapping[$rowPosition]['targetIntegrationField'] == $integrationValue) {
                        $compatibleFields[$key]['selected'] = true;
                    }
                }
            }

            $allTargetElementFieldOptions[$rowPosition] = $compatibleFields;

            $rowPosition++;
        }

        $allTargetElementFieldOptions = $this->removeUnnecessaryOptgroups($allTargetElementFieldOptions);

        return $allTargetElementFieldOptions;
    }

    /**
     * @param $allTargetElementFieldOptions
     *
     * @return array
     */
    private function removeUnnecessaryOptgroups($allTargetElementFieldOptions): array
    {
        $aux = [];
        // Removes optgroups with no fields
        foreach ($allTargetElementFieldOptions as $rowIndex => $targetElementFieldOptions) {
            foreach ($targetElementFieldOptions as $key => $dropdownOption) {
                if (isset($dropdownOption['optgroup'])) {

                    if (isset($targetElementFieldOptions[$key + 1]['value'])) {
                        $aux[$rowIndex][] = $targetElementFieldOptions[$key];
                    }
                } else {
                    $aux[$rowIndex][] = $dropdownOption;
                }
            }
        }

        return $aux;
    }

    /**
     * @param array $formFields
     * @param       $entryField
     *
     * @return array
     */
    private function getCompatibleFields(array $formFields, $entryField): array
    {
        $finalOptions = [];

        foreach ($formFields as $pos => $field) {
            if (isset($field['optgroup'])) {
                $finalOptions[] = $field;
                continue;
            }
            $compatibleFields = $field['compatibleCraftFields'] ?? '*';
            // Check default attributes
            if (isset($entryField['class'])) {
                if (is_array($compatibleFields) &&
                    !in_array($entryField['class'], $compatibleFields, true)) {
                    $field = null;
                }

                if ($field) {
                    $finalOptions[] = $field;
                }
            }
        }

        return $finalOptions;
    }

    /**
     * @param bool             $success
     * @param Integration|null $integration
     *
     * @return Response
     */
    private function returnJson(bool $success, Integration $integration = null): Response
    {
        // @todo how we should return errors to the edit integration modal? template response is disabled for now
        return $this->asJson([
            'success' => $success,
            'errors' => $integration ? $integration->getErrors() : null,
            'integration' => [
                'id' => $integration->id,
                'name' => $integration->name ?? null,
                'enabled' => $integration->enabled
            ],
            //'template' => $success ? false : SproutForms::$app->integrations->getModalIntegrationTemplate($integration),
        ]);
    }
}
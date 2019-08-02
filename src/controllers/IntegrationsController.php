<?php

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\base\ElementIntegration;
use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use Craft;

use craft\errors\MissingComponentException;
use craft\web\Controller as BaseController;
use barrelstrength\sproutforms\SproutForms;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class sIntegrationsController extends BaseController
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
        $integration->sendRule = $request->getBodyParam('sendRule');

        $settings = $request->getBodyParam('settings.'.$type);

        $integration = SproutForms::$app->integrations->createIntegration([
            'id' => $integration->id,
            'formId' => $integration->formId,
            'name' => $integration->name,
            'enabled' => $integration->enabled,
            'sendRule' => $integration->sendRule,
            'type' => get_class($integration),
            'settings' => $settings,
        ]);

        $integration = new $type($integration);

        if (!SproutForms::$app->integrations->saveIntegration($integration)) {
            Craft::error('Unable to save integration.', __METHOD__);
            return $this->returnJson(false);
        }

        Craft::info('Integration Saved', __METHOD__);

        return $this->returnJson(true, $integration);
    }

    /**
     * Edits an existing integration.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MissingComponentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionEditIntegration(): Response
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $integrationId = $request->getBodyParam('integrationId');

        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);

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

        $integration->formId = $request->getBodyParam('formId');

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
        $response = false;

        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');
        $integration = IntegrationRecord::findOne($integrationId);

        if ($integration) {
            $response = $integration->delete();
        }

        return $this->asJson([
            'success' => $response
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function actionGetElementEntryFields(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $entryTypeId = Craft::$app->request->getRequiredBodyParam('entryTypeId');
        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');

        /** @var ElementIntegration $integration */
        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);

        $entryFields = $integration->getElementCustomFieldsAsOptions($entryTypeId);
        $entryFieldsByRow = $this->getFieldsAsOptionsByRow($entryFields, $integration, $entryTypeId);

        return $this->asJson([
            'success' => true,
            'fieldOptionsByRow' => $entryFieldsByRow
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function actionGetFormFields(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');
        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);

        if (!$integration) {
            return $this->asJson([
                'success' => false,
                'formFields' => []
            ]);
        }

        $formFields = $integration->getFormFieldsAsMappingOptions();

        return $this->asJson([
            'success' => true,
            'formFields' => $formFields
        ]);
    }

    /**
     * @param             $entryFields
     * @param Integration $integration
     * @param             $entryTypeId
     *
     * @return array
     * @throws InvalidConfigException
     */
    private function getFieldsAsOptionsByRow($entryFields, $integration, $entryTypeId): array
    {
        $fieldMapping = $integration->fieldMapping;
        $integrationSectionId = $integration->entryTypeId ?? null;

        $formFields = $integration->getFormFieldsAsMappingOptions();
        $rowPosition = 0;
        $finalOptions = [];

        foreach ($formFields as $formField) {
            $optionsByRow = $this->getCompatibleFields($entryFields, $formField);
            // We have rows stored and are for the same sectionType
            if ($fieldMapping && ($integrationSectionId == $entryTypeId) &&
                isset($fieldMapping[$rowPosition])) {
                foreach ($optionsByRow as $key => $option) {
                    if ($option['value'] == $fieldMapping[$rowPosition]['targetIntegrationField'] &&
                        $fieldMapping[$rowPosition]['sourceFormField'] == $formField['value']) {
                        $optionsByRow[$key]['selected'] = true;
                    }
                }
            }

            $finalOptions[$rowPosition] = $optionsByRow;

            $rowPosition++;
        }

        return $finalOptions;
    }

    /**
     * @param array $entryFields
     * @param array $formField
     *
     * @return array
     */
    private function getCompatibleFields(array $entryFields, array $formField): array
    {
        $compatibleFields = $formField['compatibleCraftFields'] ?? '*';
        $finalOptions = [];

        foreach ($entryFields as $field) {
            $option = [
                'label' => $field->name.' ('.$field->handle.')',
                'value' => $field->handle
            ];

            if (is_array($compatibleFields) &&
                !in_array(get_class($field), $compatibleFields, true)) {
                $option = null;
            }

            if ($option) {
                $finalOptions[] = $option;
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
<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\base\ElementIntegration;
use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller as BaseController;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
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
     */
    public function actionSaveIntegration(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $type = $request->getRequiredBodyParam('type');

        /** @var Integration $integration */
        $integration = SproutForms::$app->integrations->createIntegration([
            'id' => $request->getBodyParam('integrationId'),
            'formId' => $request->getBodyParam('formId'),
            'name' => $request->getBodyParam('name'),
            'enabled' => $request->getBodyParam('enabled'),
            'sendRule' => $request->getBodyParam('sendRule'),
            'type' => $type,
            'settings' => $request->getBodyParam('settings.'.$type)
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
     * @throws Exception
     * @throws Exception
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
     * Returns an array of Form Fields to display in the Integration Modal Source Fields column
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function actionGetSourceFormFields(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');
        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);

        if (!$integration) {
            return $this->asJson([
                'success' => false,
                'sourceFormFields' => []
            ]);
        }

        $sourceFormFields = $integration->getSourceFormFieldsAsMappingOptions();

        return $this->asJson([
            'success' => true,
            'sourceFormFields' => $sourceFormFields
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function actionGetTargetIntegrationFields(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $integrationId = Craft::$app->request->getRequiredBodyParam('integrationId');

        /** @var ElementIntegration $integration */
        $integration = SproutForms::$app->integrations->getIntegrationById($integrationId);
        $integrationType = Craft::$app->getRequest()->getBodyParam('type');

        // Grab the current form values from the serialized ajax request instead of from POST
        $params = Craft::$app->getRequest()->getBodyParam('settings.'.$integrationType);

        // Ignore the current field mapping, as we're changing that
        unset($params['fieldMapping']);

        // Assign any current form values that match properties the $integration model
        foreach ($params as $key => $value) {
            if (property_exists($integration, $key)) {
                $integration->{$key} = $value;
            }
        }

        $targetIntegrationFields = $integration->getTargetIntegrationFieldsAsMappingOptions();

        return $this->asJson([
            'success' => true,
            'targetIntegrationFields' => $targetIntegrationFields
        ]);
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
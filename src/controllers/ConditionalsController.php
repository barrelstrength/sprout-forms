<?php

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\base\ConditionalLogic;
use barrelstrength\sproutforms\records\ConditionalLogic as ConditionalLogicRecord;
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

class ConditionalsController extends BaseController
{
    /**
     * Enable or disable an Conditional
     *
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionEnableConditional(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $conditionalId = $request->getBodyParam('conditionalId');
        $enabled = $request->getBodyParam('enabled');
        $enabled = $enabled == 1;

        $pieces = explode('-', $conditionalId);

        if (count($pieces) == 3) {
            $conditional = SproutForms::$app->conditionals->getConditionalById($pieces[2]);
            if ($conditional) {
                $conditional->enabled = $enabled;
                if (SproutForms::$app->conditionals->saveConditional($conditional)) {
                    return $this->returnJson(true, $conditional);
                }
            }
        }

        return $this->asJson([
            'success' => false
        ]);
    }

    /**
     * Save an Conditional
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionSaveConditional(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $type = $request->getRequiredBodyParam('type');

        /** @var Conditional $conditional */
        $conditional = new $type();

        $conditional->id = $request->getBodyParam('conditionalId');
        $conditional->formId = $request->getBodyParam('formId');
        $conditional->name = $request->getBodyParam('name');
        $conditional->enabled = $request->getBodyParam('enabled');
        $conditional->rules = $request->getBodyParam('sproutformsrules');
        $conditional->behaviorAction = $request->getBodyParam('behaviorAction');
        $conditional->behaviorTarget = $request->getBodyParam('behaviorTarget');

        $settings = $request->getBodyParam('settings.'.$type);

        $conditional = SproutForms::$app->conditionals->createConditional([
            'id' => $conditional->id,
            'formId' => $conditional->formId,
            'name' => $conditional->name,
            'enabled' => $conditional->enabled,
            'rules' => $conditional->rules,
            'behaviorAction' => $conditional->behaviorAction,
            'behaviorTarget' => $conditional->behaviorTarget,
            'type' => get_class($conditional),
            'settings' => $settings,
        ]);

        $conditional = new $type($conditional);

        if (!SproutForms::$app->conditionals->saveConditional($conditional)) {
            Craft::error('Unable to save conditional.', __METHOD__);
            return $this->returnJson(false);
        }

        Craft::info('Conditional Saved', __METHOD__);

        return $this->returnJson(true, $conditional);
    }

    /**
     * Edits an existing conditional.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws MissingComponentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function actionEditConditional(): Response
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $conditionalId = $request->getBodyParam('conditionalId');

        $conditional = SproutForms::$app->conditionals->getConditionalById($conditionalId);

        if ($conditional === null) {
            $message = Craft::t('sprout-forms', 'No conditional found with id: {id}', [
                'id' => $conditionalId
            ]);

            Craft::error($message, __METHOD__);

            return $this->asJson([
                'success' => false,
                'error' => $message,
            ]);
        }

        $conditional->formId = $request->getBodyParam('formId');

        return $this->asJson([
            'success' => true,
            'errors' => $conditional->getErrors(),
            'conditional' => [
                'id' => $conditional->id,
                'name' => $conditional->name
            ],
            'template' => SproutForms::$app->conditionals->getModalConditionalTemplate($conditional),
        ]);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteConditional(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $response = false;

        $conditionalId = Craft::$app->request->getRequiredBodyParam('conditionalId');
        $conditional = ConditionalLogicRecord::findOne($conditionalId);

        if ($conditional) {
            $response = $conditional->delete();
        }

        return $this->asJson([
            'success' => $response
        ]);
    }

    /**
     * @param bool             $success
     * @param ConditionalLogic|null $conditional
     *
     * @return Response
     */
    private function returnJson(bool $success, ConditionalLogic $conditional = null): Response
    {
        // @todo how we should return errors to the edit conditional modal? template response is disabled for now
        return $this->asJson([
            'success' => $success,
            'errors' => $conditional ? $conditional->getErrors() : null,
            'conditional' => [
                'id' => $conditional->id,
                'name' => $conditional->name ?? null,
                'enabled' => $conditional->enabled
            ],
            //'template' => $success ? false : SproutForms::$app->conditionals->getModalConditionalTemplate($conditional),
        ]);
    }
}
<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\base\ConditionInterface;
use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\base\Rule;
use barrelstrength\sproutforms\records\Rules as RulesRecord;
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

class RulesController extends BaseController
{
    protected $allowAnonymous = [
        'validate-condition'
    ];

    /**
     * Save an Rule
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function actionSaveRule(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $type = $request->getRequiredBodyParam('type');

        /** @var Rule $rule */
        $rule = new $type();

        $rule->id = $request->getBodyParam('ruleId');
        $rule->formId = $request->getBodyParam('formId');
        $rule->name = $request->getBodyParam('name');
        $rule->enabled = $request->getBodyParam('enabled');
        $rule->behaviorAction = $request->getBodyParam('behaviorAction');
        $rule->behaviorTarget = $request->getBodyParam('behaviorTarget');

        $settings = $request->getBodyParam('settings.'.$type);

        $rule = SproutForms::$app->rules->createRule([
            'id' => $rule->id,
            'formId' => $rule->formId,
            'name' => $rule->name,
            'enabled' => $rule->enabled === '1',
            'behaviorAction' => $rule->behaviorAction,
            'behaviorTarget' => $rule->behaviorTarget,
            'type' => get_class($rule),
            'settings' => $settings,
        ]);

        $rule = new $type($rule);

        if (!SproutForms::$app->rules->saveRule($rule)) {
            Craft::error('Unable to save rule.', __METHOD__);

            return $this->returnJson(false);
        }

        Craft::info('Rule Saved', __METHOD__);

        return $this->returnJson(true, $rule);
    }

    /**
     * Edits an existing Rule.
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
    public function actionEditRule(): Response
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $ruleId = $request->getBodyParam('ruleId');

        $rule = SproutForms::$app->rules->getRuleById($ruleId);

        if ($rule === null) {
            $message = Craft::t('sprout-forms', 'No rule found with id: {id}', [
                'id' => $ruleId
            ]);

            Craft::error($message, __METHOD__);

            return $this->asJson([
                'success' => false,
                'error' => $message,
            ]);
        }

        $rule->formId = $request->getBodyParam('formId');

        return $this->asJson([
            'success' => true,
            'errors' => $rule->getErrors(),
            'conditional' => [
                'id' => $rule->id,
                'name' => $rule->name
            ],
            'template' => SproutForms::$app->rules->getRulesModal($rule),
        ]);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteRule(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $response = false;

        $ruleId = Craft::$app->request->getRequiredBodyParam('ruleId');
        $rule = RulesRecord::findOne($ruleId);

        if ($rule) {
            $response = $rule->delete();
        }

        return $this->asJson([
            'success' => $response
        ]);
    }

    /**
     * Enable or disable a Rule
     *
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionEnableRule(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $ruleId = $request->getBodyParam('ruleId');
        $enabled = $request->getBodyParam('enabled');
        $enabled = $enabled == 1;

        $pieces = explode('-', $ruleId);
        $ruleId = $pieces[2];

        if (count($pieces) === 3) {
            $rule = SproutForms::$app->rules->getRuleById($ruleId);
            if ($rule) {
                $rule->enabled = $enabled;
                if (SproutForms::$app->rules->saveRule($rule)) {
                    return $this->returnJson(true, $rule);
                }
            }
        }

        return $this->asJson([
            'success' => false
        ]);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionGetConditionValueInputHtml(): Response
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $formId = $request->getBodyParam('formId');
        $condition = $request->getBodyParam('condition');
        $inputName = $request->getBodyParam('inputName');
        $inputValue = $request->getBodyParam('inputValue');
        $formFieldHandle = $request->getBodyParam('formFieldHandle');

        $form = SproutForms::$app->forms->getFormById($formId);

        /** @var FormField $formField */
        $formField = $form->getField($formFieldHandle);
        $conditionClass = new $condition();

        return $this->asJson([
            'success' => true,
            'html' => $formField->getConditionValueInputHtml($conditionClass, $inputName, $inputValue)
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionValidateCondition(): Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $results = [];

        $rules = json_decode($request->getBodyParam('rules'), true);

        foreach ($rules['data'] as $targetField => $andRules) {
            $andResult = true;
            foreach ($andRules as $orRules) {
                $orResult = false;
                foreach ($orRules as $orRule) {
                    $condition = new $orRule['condition'];
                    $condition->inputValue = $orRule['inputValue'];
                    $condition->ruleValue = $orRule['ruleValue'];
                    /** @var ConditionInterface $condition */
                    $result = $condition->validate();
                    if ($result) {
                        $orResult = true;
                    }
                }
                $andResult = $andResult && $orResult;
            }
            $results[$targetField] = $andResult;
        }

        return $this->asJson([
            'success' => true,
            'result' => $results
        ]);
    }

    /**
     * @param bool      $success
     * @param Rule|null $rule
     *
     * @return Response
     */
    private function returnJson(bool $success, Rule $rule = null): Response
    {
        return $this->asJson([
            'success' => $success,
            'errors' => $rule ? $rule->getErrors() : null,
            'rule' => [
                'id' => $rule->id,
                'name' => $rule->name ?? null,
                'enabled' => $rule->enabled,
                'behavior' => $rule->getBehaviorDescription()
            ]
        ]);
    }
}
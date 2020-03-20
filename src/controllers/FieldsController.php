<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\controllers;


use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\Field;
use craft\errors\ElementNotFoundException;
use craft\helpers\Json;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use craft\web\Controller as BaseController;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FieldsController extends BaseController
{
    /**
     * This action allows to load the modal field template.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws ElementNotFoundException
     * @throws InvalidConfigException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws Exception
     */
    public function actionModalField(): Response
    {
        $this->requireAcceptsJson();
        $formId = Craft::$app->getRequest()->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if (!$form) {
            throw new ElementNotFoundException('Form not found.');
        }

        return $this->asJson(SproutForms::$app->fields->getModalFieldTemplate($form));
    }

    /**
     * This action allows create a default field given a type.
     *
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionCreateField(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $tabId = $request->getBodyParam('tabId');

        $tab = FieldLayoutTabRecord::findOne($tabId);
        $formId = $request->getBodyParam('formId');
        $nextId = $request->getBodyParam('nextId');

        $form = SproutForms::$app->forms->getFormById($formId);

        if ($type && $form && $tab) {
            /** @var Field $field */
            $field = SproutForms::$app->fields->createDefaultField($type, $form);

            if ($field) {
                // Set the field layout
                $oldFieldLayout = $form->getFieldLayout();
                $oldTabs = $oldFieldLayout->getTabs();

                if ($oldTabs) {
                    // it's a new field
                    $response = SproutForms::$app->fields->addFieldToLayout($field, $form, $tabId, $nextId);

                    return $this->returnJson($response, $field, $form, $tab->name, $tabId);
                }
            }
        }

        // @todo - add error messages
        return $this->returnJson(false, null, $form, null, $tabId);
    }

    /**
     * Save a field.
     *
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionSaveField(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sproutForms-editForms');

        $request = Craft::$app->getRequest();
        $fieldsService = Craft::$app->getFields();
        // Make sure our field has a section

        // @todo - handle this much more gracefully
        $tabId = $request->getBodyParam('tabId');

        // Get the Form these fields are related to
        $formId = $request->getRequiredBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if (!$form) {
            throw new NotFoundHttpException('Form not found.');
        }

        $type = $request->getRequiredBodyParam('type');
        $fieldId = $request->getBodyParam('fieldId');

        /** @var Field $field */
        $field = $fieldsService->createField([
            'type' => $type,
            'id' => $fieldId,
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'instructions' => $request->getBodyParam('instructions'),
            // @todo - confirm locales/Sites work as expected
            'translationMethod' => Field::TRANSLATION_METHOD_NONE,
            'settings' => $request->getBodyParam('types.'.$type),
        ]);

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
            $fieldLayoutField->required = $required !== '';
            $fieldLayoutField->save(false);
            $field->required = $fieldLayoutField->required;
        }

        // Set our field context
        Craft::$app->content->fieldContext = $form->getFieldContext();
        Craft::$app->content->contentTable = $form->getContentTable();

        // Save a new field
        if ($field->id) {
            $isNewField = false;
            /** @var Field $oldField */
            $oldField = Craft::$app->fields->getFieldById($field->id);
            $oldHandle = $oldField->handle;
        } else {
            $isNewField = true;
            $oldHandle = null;
        }

        // Save our field
        if (!$fieldsService->saveField($field)) {
            // Does not validate
            Craft::error('Field does not validate.', __METHOD__);

            $variables['tabId'] = $tabId;
            $variables['field'] = $field;

            return $this->returnJson(false, $field, $form, null, $tabId);
        }

        // Check if the handle is updated to also update the titleFormat, rules and integrations
        if (!$isNewField && $oldHandle !== $field->handle) {
            if (strpos($form->titleFormat, $oldHandle) !== false) {
                $newTitleFormat = SproutForms::$app->forms->updateTitleFormat($oldHandle, $field->handle, $form->titleFormat);
                $form->titleFormat = $newTitleFormat;
            }

            SproutForms::$app->forms->updateFieldOnFieldRules($oldHandle, $field->handle, $form);
            SproutForms::$app->forms->updateFieldOnIntegrations($oldHandle, $field->handle, $form);
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
            Craft::info('Field Saved', __METHOD__);
            SproutForms::$app->forms->saveForm($form);

            return $this->returnJson(true, $field, $form, $tabName, $tabId);
        }

        $variables['tabId'] = $tabId;
        $variables['field'] = $field;
        Craft::error("Couldn't save field.", __METHOD__);
        Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'Couldn’t save field.'));

        return $this->returnJson(false, $field, $form);
    }

    /**
     * Edits an existing field.
     *
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws Exception
     * @throws Exception
     */
    public function actionEditField(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editForms');

        $request = Craft::$app->getRequest();

        $id = $request->getBodyParam('fieldId');
        $formId = $request->getBodyParam('formId');

        /** @var Form $form */
        $form = SproutForms::$app->forms->getFormById($formId);
        /** @var Field $field */
        $field = Craft::$app->fields->getFieldById($id);

        if ($field) {
            $fieldLayoutField = FieldLayoutFieldRecord::findOne([
                'fieldId' => $field->id,
                'layoutId' => $form->fieldLayoutId
            ]);

            $field->required = $fieldLayoutField->required;

            $group = FieldLayoutTabRecord::findOne($fieldLayoutField->tabId);

            return $this->asJson([
                'success' => true,
                'errors' => $field->getErrors(),
                'field' => [
                    'id' => $field->id,
                    'name' => $field->name,
                    'handle' => $field->handle,
                    'instructions' => $field->instructions,
                    'required' => $field->required,
                    //'translatable' => $field->translatable,
                    'group' => [
                        'name' => $group->name,
                    ],
                ],
                'template' => SproutForms::$app->fields->getModalFieldTemplate($form, $field, $group->id),
            ]);
        }

        $message = Craft::t('sprout-forms', 'The field requested to edit no longer exists.');
        Craft::error($message, __METHOD__);

        return $this->asJson([
            'success' => false,
            'error' => $message,
        ]);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteField(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

        $fieldId = Craft::$app->request->getRequiredBodyParam('fieldId');

        /** @var Field $field */
        $field = Craft::$app->fields->getFieldById($fieldId);
        $oldHandle = $field->handle;
        $formId = Craft::$app->request->getRequiredBodyParam('formId');

        /** @var Form $form */
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
            SproutForms::$app->forms->removeFieldRulesUsingField($oldHandle, $form);

            return $this->asJson([
                'success' => true
            ]);
        }

        return $this->asJson([
            'success' => false
        ]);
    }

    /**
     * Reorder a field
     *
     * @return Response
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionReorderFields(): Response
    {
        $this->requireAdmin(false);
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

        $fieldIds = Json::decode(Craft::$app->request->getRequiredBodyParam('ids'));
        SproutForms::$app->fields->reorderFields($fieldIds);

        return $this->asJson([
            'success' => true
        ]);
    }

    /**
     * @param bool $success
     * @param      $field
     * @param Form $form
     * @param null $tabName
     * @param null $tabId
     *
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     * @throws Exception
     * @throws Exception
     */
    private function returnJson(bool $success, $field, Form $form, $tabName = null, $tabId = null): Response
    {
        /** @var FormField $field */
        return $this->asJson([
            'success' => $success,
            'errors' => $field ? $field->getErrors() : null,
            'field' => [
                'id' => $field->id,
                'name' => $field->name,
                'handle' => $field->handle,
                'icon' => $field->getSvgIconPath(),
                'htmlExample' => $field->getExampleInputHtml(),
                'required' => $field->required,
                'instructions' => $field->instructions,
                'group' => [
                    'name' => $tabName,
                    'id' => $tabId
                ],
            ],
            'template' => $success ? false : SproutForms::$app->fields->getModalFieldTemplate($form, $field),
        ]);
    }
}
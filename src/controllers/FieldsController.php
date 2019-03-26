<?php

namespace barrelstrength\sproutforms\controllers;


use barrelstrength\sproutforms\elements\Form;
use Craft;
use craft\helpers\Json;
use craft\web\Controller as BaseController;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
use craft\base\Field;
use barrelstrength\sproutforms\SproutForms;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FieldsController extends BaseController
{
    /**
     * This action allows to load the modal field template.
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionModalField(): Response
    {
        $this->requireAcceptsJson();
        $formId = Craft::$app->getRequest()->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        return $this->asJson(SproutForms::$app->fields->getModalFieldTemplate($form));
    }

    /**
     * This action allows create a default field given a type.
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
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
     * This action allows create a new Tab to current layout
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAddTab(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

        $request = Craft::$app->getRequest();
        $name = $request->getBodyParam('name');
        $sortOrder = $request->getBodyParam('sortOrder');
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        $tab = null;

        if ($name && $form && $sortOrder) {
            $tab = SproutForms::$app->fields->createNewTab($name, $sortOrder, $form);

            if ($tab->id) {
                return $this->asJson([
                    'success' => true,
                    'tab' => [
                        'id' => $tab->id,
                        'name' => $tab->name
                    ]
                ]);
            }
        }

        return $this->asJson([
            'success' => false,
            'errors' => $tab->getErrors()
        ]);
    }

    /**
     * This action allows delete a Tab of the current layout
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteTab(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

        $request = Craft::$app->getRequest();
        $tabId = $request->getBodyParam('tabId');
        $tabId = str_replace('tab-', '', $tabId);
        $tabRecord = FieldLayoutTabRecord::findOne($tabId);

        if ($tabRecord) {
            $result = $tabRecord->delete();

            if ($result) {
                return $this->asJson([
                    'success' => true
                ]);
            }
        }

        return $this->asJson([
            'success' => false,
            'errors' => $tabRecord->getErrors() ?? null
        ]);
    }

    /**
     * This action allows rename a current Tab
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionRenameTab(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

        $request = Craft::$app->getRequest();
        $name = $request->getBodyParam('name');
        $oldName = $request->getBodyParam('oldName');
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if ($name && $form) {
            $result = SproutForms::$app->fields->renameTab($name, $oldName, $form);

            if ($result) {
                return $this->asJson([
                    'success' => true
                ]);
            }
        }

        return $this->asJson([
            'success' => false,
            'errors' => Craft::t('sprout-forms', 'Unable to rename tab')
        ]);
    }

    /**
     * Save a field.
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveField(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sproutForms-editEntries');

        $request = Craft::$app->getRequest();
        $fieldsService = Craft::$app->getFields();
        // Make sure our field has a section

        // @todo - handle this much more gracefully
        $tabId = $request->getBodyParam('tabId');

        // Get the Form these fields are related to
        $formId = $request->getRequiredBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if (!$form) {
            throw new NotFoundHttpException(Craft::t('sprout-forms', 'Form not found.'));
        }

        $type = $request->getRequiredBodyParam('type');
        $fieldId = $request->getBodyParam('fieldId');

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
        if (!$field->id) {
            $isNewField = true;
            $oldHandle = null;
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
        if (!$isNewField && $oldHandle !== $field->handle && strpos($form->titleFormat, $oldHandle) !== false) {
            $newTitleFormat = SproutForms::$app->forms->updateTitleFormat($oldHandle, $field->handle, $form->titleFormat);
            $form->titleFormat = $newTitleFormat;
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
        }

        $variables['tabId'] = $tabId;
        $variables['field'] = $field;
        SproutForms::error("Couldn't save field.");
        Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'Couldn’t save field.'));

        return $this->returnJson(false, $field, $form);
    }

    /**
     * Edits an existing field.
     *
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionEditField(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

        $request = Craft::$app->getRequest();

        $id = $request->getBodyParam('fieldId');
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        /**
         * @var Field $field
         */
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
        SproutForms::error($message);

        return $this->asJson([
            'success' => false,
            'error' => $message,
        ]);
    }

    /**
     * @return Response
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteField(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('sproutForms-editEntries');

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
        }

        return $this->asJson([
            'success' => false
        ]);
    }

    /**
     * Reorder a field
     *
     * @return Response
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionReorderFields(): Response
    {
        $this->requireAdmin();
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function returnJson(bool $success, $field, Form $form, $tabName = null, $tabId = null): Response
    {
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
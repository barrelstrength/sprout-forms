<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\base\ElementInterface;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use yii\web\Response;
use yii\base\Exception;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;

class FormsController extends BaseController
{
    /**
     * @throws HttpException
     */
    public function init()
    {
        $this->requirePermission('sproutForms-editForms');
        parent::init();
    }

    /**
     * Save a form
     *
     * @return null|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveForm()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $form = new FormElement();
        $duplicateForm = null;

        if ($request->getBodyParam('saveAsNew')) {
            $form->saveAsNew = true;
            $duplicateForm = SproutForms::$app->forms->createNewForm(
                $request->getBodyParam('name'),
                $request->getBodyParam('handle')
            );

            if ($duplicateForm) {
                $form->id = $duplicateForm->id;
            } else {
                throw new Exception(Craft::t('sprout-forms', 'Error creating Form'));
            }
        } else {
            $form = SproutForms::$app->forms->getFormById($request->getBodyParam('id'));
            if (!$form) {
                throw new NotFoundHttpException(Craft::t('sprout-forms', 'Form not found'));
            }
        }

        $form->groupId = $request->getBodyParam('groupId');
        $form->name = $request->getBodyParam('name');
        $form->handle = $request->getBodyParam('handle');
        $form->titleFormat = $request->getBodyParam('titleFormat');
        $form->displaySectionTitles = $request->getBodyParam('displaySectionTitles');
        $form->redirectUri = $request->getBodyParam('redirectUri');
        $form->submitAction = $request->getBodyParam('submitAction');
        $form->saveData = $request->getBodyParam('saveData', 0);
        $form->submitButtonText = $request->getBodyParam('submitButtonText');
        $form->templateOverridesFolder = $request->getBodyParam('templateOverridesFolder');
        $form->enableFileAttachments = $request->getBodyParam('enableFileAttachments');

        // Set the field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

        if ($form->saveAsNew) {
            $fieldLayout = SproutForms::$app->fields->getDuplicateLayout($duplicateForm, $fieldLayout);
        }

        $fieldLayout->type = FormElement::class;

        if (count($fieldLayout->getFields()) == 0) {
            Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'The form needs at least have one field'));

            Craft::$app->getUrlManager()->setRouteParams([
                    'form' => $form
                ]
            );

            return null;
        }

        $form->setFieldLayout($fieldLayout);

        // Delete any fields removed from the layout
        $deletedFields = $request->getBodyParam('deletedFields', []);

        if (count($deletedFields) > 0) {
            // Backup our field context and content table
            $oldFieldContext = Craft::$app->content->fieldContext;
            $oldContentTable = Craft::$app->content->contentTable;

            // Set our field content and content table to work with our form output
            Craft::$app->content->fieldContext = $form->getFieldContext();
            Craft::$app->content->contentTable = $form->getContentTable();

            $currentTitleFormat = null;

            foreach ($deletedFields as $fieldId) {
                // Each field deleted will be update the titleFormat
                $currentTitleFormat = SproutForms::$app->forms->cleanTitleFormat($fieldId);
                Craft::$app->fields->deleteFieldById($fieldId);
            }

            if ($currentTitleFormat) {
                // update the titleFormat
                $form->titleFormat = $currentTitleFormat;
            }

            // Reset our field context and content table to what they were previously
            Craft::$app->content->fieldContext = $oldFieldContext;
            Craft::$app->content->contentTable = $oldContentTable;
        }

        // Save it
        if (!SproutForms::$app->forms->saveForm($form)) {

            Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'Couldn’t save form.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'form' => $form
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Form saved.'));

        $_POST['redirect'] = str_replace('{id}', $form->id, $_POST['redirect']);

        return $this->redirectToPostedUrl($form);
    }

    /**
     * Edit a form.
     *
     * @param int|null                          $formId
     * @param FormElement|ElementInterface|null $form
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     */
    public function actionEditFormTemplate(int $formId = null, FormElement $form = null): Response
    {
        // Immediately create a new Form
        if (Craft::$app->request->getSegment(3) == 'new') {
            $form = SproutForms::$app->forms->createNewForm();

            if ($form) {
                $url = UrlHelper::cpUrl('sprout-forms/forms/edit/'.$form->id);
                return $this->redirect($url);
            }

            throw new Exception(Craft::t('sprout-forms', 'Error creating Form'));
        }

        if ($formId !== null) {
            $variables['formId'] = $formId;

            if ($form === null) {
                $variables['brandNewForm'] = false;

                $variables['groups'] = SproutForms::$app->groups->getAllFormGroups();
                $variables['groupId'] = '';

                // Get the Form
                $form = SproutForms::$app->forms->getFormById($formId);

                if (!$form) {
                    throw new NotFoundHttpException(Craft::t('sprout-forms', 'Form not found'));
                }
            }
        }

        $variables['form'] = $form;
        $variables['title'] = $form->name;
        $variables['groupId'] = $form->groupId;

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = 'sprout-forms/forms/edit/{id}';

        $variables['settings'] = Craft::$app->plugins->getPlugin('sprout-forms')->getSettings();

        return $this->renderTemplate('sprout-forms/forms/_editForm', $variables);
    }

    /**
     * Delete a Form
     *
     * @return Response
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteForm(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // Get the Form these fields are related to
        $formId = $request->getRequiredBodyParam('id');
        $form = SproutForms::$app->forms->getFormById($formId);

        // @todo - handle errors/rollBack
        SproutForms::$app->forms->deleteForm($form);

        return $this->redirectToPostedUrl($form);
    }
}

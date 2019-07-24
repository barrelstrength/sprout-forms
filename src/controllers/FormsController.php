<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\base\ElementInterface;
use craft\errors\WrongEditionException;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use Throwable;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
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
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->requirePermission('sproutForms-editForms');
        parent::init();
    }

    /**
     * @param int|null $formId
     * @param null $settingsSectionHandle
     * @return Response
     */
    public function actionSettings(int $formId = null, $settingsSectionHandle = null)
    {
        $form = SproutForms::$app->forms->getFormById($formId);

        return $this->renderTemplate('sprout-forms/forms/_settings/'.$settingsSectionHandle, [
            'form' => $form
        ]);
    }

    /**
     * Save a form
     *
     * @return null|Response
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws Throwable
     * @throws BadRequestHttpException
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
                $form->uid = $duplicateForm->uid;
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
     * @throws Throwable
     */
    public function actionEditFormTemplate(int $formId = null, FormElement $form = null): Response
    {
        $newForm = !$formId;

        // Immediately create a new Form
        if ($newForm) {

            // Make sure Pro is installed before we create a new form
            if (!SproutForms::$app->forms->canCreateForm()) {
                throw new WrongEditionException(Craft::t('sprout-forms', 'Please upgrade to Sprout Forms Pro Edition to create unlimited forms.'));
            }

            $form = SproutForms::$app->forms->createNewForm();

            if ($form) {
                $url = UrlHelper::cpUrl('sprout-forms/forms/edit/'.$form->id);
                return $this->redirect($url);
            }

            throw new Exception(Craft::t('sprout-forms', 'Unable to create new Form'));
        }

        if ($form === null && $formId !== null) {
            $form = SproutForms::$app->forms->getFormById($formId);

            if (!$form) {
                throw new NotFoundHttpException(Craft::t('sprout-forms', 'Form not found'));
            }
        }

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        return $this->renderTemplate('sprout-forms/forms/_editForm', [
            'form' => $form,
            'groupId' => $form->groupId ?? '',
            'groups' => SproutForms::$app->groups->getAllFormGroups(),
            'integrations' => SproutForms::$app->integrations->getFormIntegrations($formId),
            'settings' => $plugin->getSettings(),
            'continueEditingUrl' => 'sprout-forms/forms/edit/{id}'
        ]);
    }

    /**
     * Delete a Form
     *
     * @return Response
     * @throws \Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteForm(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // Get the Form these fields are related to
        $formId = $request->getRequiredBodyParam('id');
        $form = SproutForms::$app->forms->getFormById($formId);

        if (!$form) {
            throw new NotFoundHttpException(Craft::t('sprout-forms', 'Form not found'));
        }

        SproutForms::$app->forms->deleteForm($form);

        return $this->redirectToPostedUrl($form);
    }
}

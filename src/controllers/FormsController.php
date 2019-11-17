<?php

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\models\Settings;
use Craft;
use craft\base\ElementInterface;
use craft\errors\MissingComponentException;
use craft\errors\WrongEditionException;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use Throwable;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\base\Exception;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use barrelstrength\sproutforms\SproutForms;

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

    public function actionIndexTemplate()
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        if ($settings->enableSaveData) {
            return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('sprout-forms/'.$settings->defaultSection));
        }

        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('sprout-forms/forms'));
    }

    /**
     * @param int|null $formId
     * @param null     $settingsSectionHandle
     *
     * @return Response
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function actionSettingsTemplate(int $formId = null, $settingsSectionHandle = null): Response
    {
        $form = SproutForms::$app->forms->getFormById($formId);

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        $isPro = SproutBase::$app->settings->isEdition('sprout-forms', SproutForms::EDITION_PRO);

        return $this->renderTemplate('sprout-forms/forms/_settings/'.$settingsSectionHandle, [
            'form' => $form,
            'settings' => $plugin->getSettings(),
            'rules' => SproutForms::$app->rules->getRulesByFormId($formId),
            'ruleOptions' => SproutForms::$app->rules->getRuleOptions(),
            'integrations' => SproutForms::$app->integrations->getIntegrationsByFormId($formId),
            'isPro' => $isPro
        ]);
    }

    /**
     * Duplicates an entry.
     *
     * @return FormsController|mixed
     * @throws InvalidRouteException
     */
    public function actionDuplicateForm()
    {
        return $this->runAction('save-form', ['duplicate' => true]);
    }

    /**
     * Save a form
     *
     * @param bool $duplicate
     *
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws MissingComponentException
     * @throws NotFoundHttpException
     * @throws Throwable
     */
    public function actionSaveForm(bool $duplicate = false)
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $form = $this->getFormModel();
        $duplicateForm = null;

        // If we're duplicating the form, swap $form with the duplicate

        if ($duplicate) {
            $duplicateForm = SproutForms::$app->forms->createNewForm(
                $request->getBodyParam('name'),
                $request->getBodyParam('handle')
            );

            if ($duplicateForm) {
                $form->id = $duplicateForm->id;
                $form->uid = $duplicateForm->uid;
            } else {
                throw new Exception('Error creating Form');
            }
        }

        $this->populateFormModel($form);
        $this->prepareFieldLayout($form, $duplicate, $duplicateForm);

        // Save it
        if (!SproutForms::$app->forms->saveForm($form, $duplicate)) {

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
        $isNew = !$formId;

        // Immediately create a new Form
        if ($isNew) {

            // Make sure Pro is installed before we create a new form
            if (!SproutForms::$app->forms->canCreateForm()) {
                throw new WrongEditionException('Please upgrade to Sprout Forms Pro Edition to create unlimited forms.');
            }

            $form = SproutForms::$app->forms->createNewForm();

            if ($form) {
                $url = UrlHelper::cpUrl('sprout-forms/forms/edit/'.$form->id);
                return $this->redirect($url);
            }

            throw new Exception('Unable to create new Form');
        }

        if ($form === null && $formId !== null) {
            $form = SproutForms::$app->forms->getFormById($formId);

            if (!$form) {
                throw new NotFoundHttpException('Form not found');
            }
        }

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        return $this->renderTemplate('sprout-forms/forms/_editForm', [
            'form' => $form,
            'groups' => SproutForms::$app->groups->getAllFormGroups(),
            'groupId' => $form->groupId ?? null,
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
        $formId = $request->getRequiredBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if (!$form) {
            throw new NotFoundHttpException('Form not found');
        }

        SproutForms::$app->forms->deleteForm($form);

        return $this->redirectToPostedUrl($form);
    }

    /**
     * @param FormElement $form
     * @param bool        $duplicate
     * @param FormElement $duplicatedForm
     *
     * @throws Throwable
     */
    public function prepareFieldLayout(FormElement $form, $duplicate = false, $duplicatedForm = null)
    {
        // Set the field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

        if ($duplicate) {
            $fieldLayout = SproutForms::$app->fields->getDuplicateLayout($duplicatedForm, $fieldLayout);
        }

        // Make sure we have a layout if:
        // 1. Form fails validation due to no fields existing
        // 2. We are saving General Settings and no Layout exists
        if (count($fieldLayout->getFields()) === 0) {
            $fieldLayout = $form->getFieldLayout();
        }

        $fieldLayout->type = FormElement::class;

        $form->setFieldLayout($fieldLayout);

        // Delete any fields removed from the layout
        $deletedFields = Craft::$app->getRequest()->getBodyParam('deletedFields', []);

        if (count($deletedFields) > 0) {
            // Backup our field context and content table
            $oldFieldContext = Craft::$app->content->fieldContext;
            $oldContentTable = Craft::$app->content->contentTable;

            // Set our field content and content table to work with our form output
            Craft::$app->content->fieldContext = $form->getFieldContext();
            Craft::$app->content->contentTable = $form->getContentTable();

            $currentTitleFormat = null;

            foreach ($deletedFields as $fieldId) {
                // If a deleted field is used in the titleFormat setting, update it
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
    }

    /**
     * @return FormElement
     * @throws NotFoundHttpException
     */
    private function getFormModel(): FormElement
    {
        $request = Craft::$app->getRequest();
        $formId = $request->getBodyParam('formId');
        $siteId = $request->getBodyParam('siteId');

        if ($formId) {
            $form = SproutForms::$app->forms->getFormById($formId, $siteId);

            if (!$form) {
                throw new NotFoundHttpException('Form not found');
            }

            // Set oldHandle to the value from the db so we can
            // determine if we need to rename the content table
            $form->oldHandle = $form->handle;
        } else {
            $form = new FormElement();

            if ($siteId) {
                $form->siteId = $siteId;
            }
        }

        return $form;
    }

    /**
     * @param FormElement $form
     */
    private function populateFormModel(FormElement $form)
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        $request = Craft::$app->getRequest();

        // Set the form attributes, defaulting to the existing values for whatever is missing from the post data
        $form->groupId = $request->getBodyParam('groupId', $form->groupId);
        $form->name = $request->getBodyParam('name', $form->name);
        $form->handle = $request->getBodyParam('handle', $form->handle);
        $form->displaySectionTitles = $request->getBodyParam('displaySectionTitles', $form->displaySectionTitles);
        $form->redirectUri = $request->getBodyParam('redirectUri', $form->redirectUri);
        $form->saveData = $request->getBodyParam('saveData', $form->saveData);
        $form->submitButtonText = $request->getBodyParam('submitButtonText', $form->submitButtonText);
        $form->titleFormat = $request->getBodyParam('titleFormat', $form->titleFormat);
        $form->formTemplate = $request->getBodyParam('formTemplate', $form->formTemplate);
        $form->enableCaptchas = $request->getBodyParam('enableCaptchas', $form->enableCaptchas);

        if (!$form->titleFormat) {
            $form->titleFormat = "{dateCreated|date('D, d M Y H:i:s')}";
        }

        if (!$form->displaySectionTitles) {
            $form->displaySectionTitles = false;
        }

        if (!$form->saveData) {
            $form->saveData = false;
        }

        if ($form->formTemplate === '') {
            $form->formTemplate = $settings->formTemplateDefaultValue ?? AccessibleTemplates::class;
        }

        if (!$form->enableCaptchas) {
            $form->enableCaptchas = false;
        }
    }
}

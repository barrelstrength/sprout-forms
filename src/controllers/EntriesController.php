<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\events\OnBeforePopulateEntryEvent;
use barrelstrength\sproutforms\events\OnBeforeValidateEntryEvent;
use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\UrlHelper;
use craft\web\Controller as BaseController;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Markdown;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class EntriesController
 *
 * @package barrelstrength\sproutforms\controllers
 *
 * @property EntryElement $entryModel
 */
class EntriesController extends BaseController
{
    const EVENT_BEFORE_POPULATE = 'beforePopulate';
    const EVENT_BEFORE_VALIDATE = 'beforeValidate';

    /**
     * @var FormElement
     */
    public $form;

    /**
     * Allows anonymous execution
     *
     * @var string[]
     */
    protected $allowAnonymous = [
        'save-entry'
    ];

    public function init()
    {
        parent::init();

        $response = Craft::$app->getResponse();
        $headers = $response->getHeaders();
        $headers->set('Cache-Control', 'private');
    }

    /**
     * @return Response
     */
    public function actionEntriesIndexTemplate(): Response
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        if (!$settings->enableSaveData) {
            return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('sprout-forms/forms'));
        }

        return $this->renderTemplate('sprout-forms/entries/index');
    }

    /**
     * Route Controller for Edit Entry Template
     *
     * @param int|null          $entryId
     * @param EntryElement|null $entry
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws MissingComponentException
     * @throws InvalidConfigException
     */
    public function actionEditEntryTemplate(int $entryId = null, EntryElement $entry = null): Response
    {
        $this->requirePermission('sproutForms-editEntries');

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        if (!$settings->enableSaveData) {
            return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('sprout-forms/forms'));
        }

        if (SproutForms::$app->forms->activeCpEntry) {
            $entry = SproutForms::$app->forms->activeCpEntry;
        } else {
            if ($entry === null) {
                $entry = SproutForms::$app->entries->getEntryById($entryId);
            }

            if (!$entry) {
                throw new NotFoundHttpException('Entry not found');
            }

            Craft::$app->getContent()->populateElementContent($entry);
        }

        $form = SproutForms::$app->forms->getFormById($entry->formId);

        $saveData = SproutForms::$app->entries->isSaveDataEnabled($form);

        if (!$saveData) {
            Craft::$app->getSession()->setError(Craft::t('sprout-forms', "Unable to edit entry. Enable the 'Save Data' for this form to view, edit, or delete content."));

            return $this->renderTemplate('sprout-forms/entries');
        }

        $entryStatus = SproutForms::$app->entryStatuses->getEntryStatusById($entry->statusId);
        $statuses = SproutForms::$app->entryStatuses->getAllEntryStatuses();
        $entryStatuses = [];

        foreach ($statuses as $key => $status) {
            $entryStatuses[$status->id] = $status->name;
        }

        $variables['form'] = $form;
        $variables['entryId'] = $entryId;
        $variables['entryStatus'] = $entryStatus;
        $variables['statuses'] = $entryStatuses;

        // This is our element, so we know where to get the field values
        $variables['entry'] = $entry;

        // Get the fields for this entry
        $fieldLayoutTabs = $entry->getFieldLayout()->getTabs();

        $tabs = [];

        foreach ($fieldLayoutTabs as $tab) {
            $tabs[$tab->id]['label'] = $tab->name;
            $tabs[$tab->id]['url'] = '#tab'.$tab->sortOrder;
        }

        $variables['tabs'] = $tabs;
        $variables['fieldLayoutTabs'] = $fieldLayoutTabs;

        return $this->renderTemplate('sprout-forms/entries/_edit', $variables);
    }

    /**
     * Processes form submissions
     *
     * @return null|Response
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws \Exception
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionSaveEntry()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        if ($request->getIsCpRequest()) {
            $this->requirePermission('sproutForms-editEntries');
        }

        $formHandle = $request->getRequiredBodyParam('handle');
        $this->form = $this->form == null ? SproutForms::$app->forms->getFormByHandle($formHandle) : $this->form;

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        if ($this->form === null) {
            throw new Exception('No form exists with the handle '.$formHandle);
        }

        $event = new OnBeforePopulateEntryEvent([
            'form' => $this->form
        ]);

        $this->trigger(self::EVENT_BEFORE_POPULATE, $event);

        $entry = $this->getEntryModel();

        Craft::$app->getContent()->populateElementContent($entry);

        $this->addHiddenValuesBasedOnFieldRules($entry);

        // Populate the entry with post data
        $this->populateEntryModel($entry);

        $statusId = $request->getBodyParam('statusId');
        $entryStatus = SproutForms::$app->entryStatuses->getDefaultEntryStatus();
        $entry->statusId = $statusId ?? $entry->statusId ?? $entryStatus->id;

        // Render the Entry Title
        try {
            $entry->title = Craft::$app->getView()->renderObjectTemplate($this->form->titleFormat, $entry);
        } catch (\Exception $e) {
            Craft::error('Title format error: '.$e->getMessage(), __METHOD__);
        }

        $event = new OnBeforeValidateEntryEvent([
            'form' => $this->form,
            'entry' => $entry
        ]);

        // Captchas are processed and added to
        $this->trigger(self::EVENT_BEFORE_VALIDATE, $event);

        $entry->validate(null, false);

        // Allow override of redirect URL on failure
        if (Craft::$app->getRequest()->getBodyParam('redirectOnFailure') !== '') {
            $_POST['redirect'] = Craft::$app->getRequest()->getBodyParam('redirectOnFailure');
        }

        if ($entry->hasErrors()) {
            // Redirect back to form with validation errors
            return $this->redirectWithValidationErrors($entry);
        }

        // If we don't have errors or SPAM
        $success = true;

        if ($entry->hasCaptchaErrors()) {
            $entry->statusId = SproutForms::$app->entryStatuses->getSpamStatusId();
        }

        $saveData = SproutForms::$app->entries->isSaveDataEnabled($this->form, $entry->getIsSpam());

        // Save Data and Trigger the onSaveEntryEvent
        // This saves both valid and spam entries
        // Integrations run on EntryElement::EVENT_AFTER_SAVE Event
        if ($saveData) {
            $success = SproutForms::$app->entries->saveEntry($entry);

            if ($entry->hasCaptchaErrors()) {
                SproutForms::$app->entries->logEntriesSpam($entry);
            }
        } else {
            $isNewEntry = !$entry->id;
            SproutForms::$app->entries->callOnSaveEntryEvent($entry, $isNewEntry);
        }

        SproutForms::$app->entries->runPurgeSpamElements();

        if (!$success || $this->isSpamAndHasRedirectBehavior($entry, $settings)) {
            return $this->redirectWithValidationErrors($entry);
        }

        $this->createLastEntryId($entry);

        $successMessageTemplate = $entry->getForm()->successMessage ?? '';
        $successMessage = Craft::$app->getView()->renderObjectTemplate($successMessageTemplate, $entry);

        if (Craft::$app->getRequest()->getAcceptsJson()) {

            return $this->asJson([
                'success' => true,
                'message' => Markdown::process($successMessage),
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Entry saved.'));

        return $this->redirectToPostedUrl($entry);
    }

    /**
     * @return Response
     * @throws Throwable
     * @throws BadRequestHttpException
     */
    public function actionDeleteEntry(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('sproutForms-editEntries');

        $request = Craft::$app->getRequest();

        // Get the Entry
        $entryId = $request->getRequiredBodyParam('entryId');

        Craft::$app->elements->deleteElementById($entryId);

        return $this->redirectToPostedUrl();
    }

    /**
     * Removes field values from POST request if a Field Rule defines a given field to hidden
     *
     * @param EntryElement $entry
     *
     * @return bool
     * @throws InvalidConfigException
     */
    private function addHiddenValuesBasedOnFieldRules(EntryElement $entry): bool
    {
        if ($this->form === null) {
            return false;
        }

        $postFields = $_POST['fields'] ?? [];
        $postFieldHandles = array_keys($postFields);
        $formFields = $this->form->getFields();
        $hiddenFields = [];

        foreach ($formFields as $formField) {
            if (!in_array($formField->handle, $postFieldHandles, true)) {
                $hiddenFields[] = $formField->handle;
            }
        }

        $entry->setHiddenFields($hiddenFields);

        return true;
    }


    /**
     * Populate a EntryElement with post data
     *
     * @access private
     *
     * @param EntryElement $entry
     */
    private function populateEntryModel(EntryElement $entry)
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        $request = Craft::$app->getRequest();

        // Our EntryElement requires that we assign it a FormElement id
        $entry->formId = $this->form->id;
        $entry->ipAddress = $settings->trackRemoteIp ? $request->getRemoteIP() : null;
        $entry->referrer = $request->getReferrer();
        $entry->userAgent = $request->getUserAgent();

        // Set the entry attributes, defaulting to the existing values for whatever is missing from the post data
        $fieldsLocation = $request->getBodyParam('fieldsLocation', 'fields');

        $entry->setFieldValuesFromRequest($fieldsLocation);
        $entry->setFieldParamNamespace($fieldsLocation);
    }

    /**
     * Fetch or create a EntryElement class
     *
     * @return EntryElement
     * @throws Exception
     */
    private function getEntryModel(): EntryElement
    {
        $entryId = null;
        $request = Craft::$app->getRequest();

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $settings = $plugin->getSettings();

        if ($request->getIsCpRequest() || $settings->enableEditFormEntryViaFrontEnd) {
            $entryId = $request->getBodyParam('entryId');
        }

        if (!$entryId) {
            return new EntryElement();
        }

        $entry = SproutForms::$app->entries->getEntryById($entryId);

        if (!$entry) {
            $message = Craft::t('sprout-forms', 'No form entry exists with the given ID: {id}', [
                'entryId' => $entryId
            ]);
            throw new Exception($message);
        }

        return $entry;
    }

    /**
     * @param EntryElement $entry
     *
     * @return Response|null
     * @throws Exception
     * @throws MissingComponentException
     * @throws Throwable
     */
    private function redirectWithValidationErrors(Entry $entry)
    {
        Craft::error($entry->getErrors(), __METHOD__);

        // Handle CP requests in a CP-friendly way
        if (Craft::$app->getRequest()->getIsCpRequest()) {

            Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'Couldn’t save entry.'));

            // Store this Entry Model in a variable in our Service layer so that
            // we can access the error object from our actionEditEntryTemplate() method
            SproutForms::$app->forms->activeCpEntry = $entry;

            Craft::$app->getUrlManager()->setRouteParams([
                'entry' => $entry
            ]);

            return null;
        }

        // Respond to ajax requests with JSON
        if (Craft::$app->getRequest()->getAcceptsJson()) {

            $errorMessageTemplate = $entry->getForm()->errorMessage ?? '';
            $errorMessage = Craft::$app->getView()->renderObjectTemplate($errorMessageTemplate, $entry);

            return $this->asJson([
                'success' => false,
                'errorDisplayMethod' => $entry->getForm()->errorDisplayMethod,
                'message' => Markdown::process($errorMessage),
                'errors' => $entry->getErrors(),
            ]);
        }

        // Front-end Requests need to be a bit more dynamic

        // Store this Entry Model in a variable in our Service layer so that
        // we can access the error object from our displayForm() variable
        SproutForms::$app->forms->activeEntries[$this->form->handle] = $entry;

        // Return the form using it's name as a variable on the front-end
        Craft::$app->getUrlManager()->setRouteParams([
            $this->form->handle => $entry
        ]);

        return null;
    }

    /**
     * @param EntryElement $entry
     * @param Settings     $settings
     *
     * @return bool
     */
    private function isSpamAndHasRedirectBehavior(Entry $entry, Settings $settings): bool
    {
        if (!$entry->hasCaptchaErrors()) {
            return false;
        }

        if ($settings->spamRedirectBehavior === Settings::SPAM_REDIRECT_BEHAVIOR_NORMAL) {
            return false;
        }

        return true;
    }

    /**
     * @param $entry
     *
     * @throws MissingComponentException
     */
    private function createLastEntryId($entry)
    {
        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            // Store our new entry so we can recreate the Entry object on our thank you page
            Craft::$app->getSession()->set('lastEntryId', $entry->id);
        }
    }
}

<?php

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\elements\Entry;
use Craft;
use craft\web\Controller as BaseController;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\events\OnBeforePopulateEntryEvent;
use yii\web\Response;

class EntriesController extends BaseController
{
    const EVENT_BEFORE_POPULATE = 'beforePopulate';

    /**
     * Allows anonymous execution
     *
     * @var string[]
     */
    protected $allowAnonymous = [
        'save-entry'
    ];

    /**
     * @var FormElement
     */
    public $form;

    protected $saveData;

    public function init()
    {
        $response = Craft::$app->getResponse();
        $headers = $response->getHeaders();
        $headers->set('Cache-Control', 'private');
    }

    /**
     * Processes form submissions
     *
     * @return null|Response
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveEntry()
    {
        $this->requirePostRequest();

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            $currentUser = Craft::$app->getUser()->getIdentity();
            if (!$currentUser->can('editSproutFormsEntries')) {
                throw new ForbiddenHttpException(Craft::t('sprout-forms', "Your account doesn't have permission to edit Form Entries."));
            }
        }

        $request = Craft::$app->getRequest();
        $view = Craft::$app->getView();

        $formHandle = $request->getRequiredBodyParam('handle');
        $this->form = SproutForms::$app->forms->getFormByHandle($formHandle);

        if ($this->form === null) {
            throw new Exception(Craft::t('sprout-forms', 'No form exists with the handle '.$formHandle));
        }

        $event = new OnBeforePopulateEntryEvent([
            'form' => $this->form
        ]);

        $this->trigger(self::EVENT_BEFORE_POPULATE, $event);

        $entry = $this->getEntryModel();

        Craft::$app->getContent()->populateElementContent($entry);

        $statusId = $request->getBodyParam('statusId');

        if ($statusId !== null) {
            $entry->statusId = $statusId;
        }

        // Populate the entry with post data
        $this->populateEntryModel($entry);

        // Swap out any dynamic variables for our notifications
        // @todo - revisit with Sprout Email
        if ($this->form->notificationEnabled) {
            $this->form->notificationRecipients = $view->renderObjectTemplate($this->form->notificationRecipients, $entry);
            $this->form->notificationSubject = $view->renderObjectTemplate($this->form->notificationSubject, $entry);
            $this->form->notificationSenderName = $view->renderObjectTemplate($this->form->notificationSenderName, $entry);
            $this->form->notificationSenderEmail = $view->renderObjectTemplate($this->form->notificationSenderEmail, $entry);
            $this->form->notificationReplyToEmail = $view->renderObjectTemplate($this->form->notificationReplyToEmail, $entry);
        }

        $this->saveData = SproutForms::$app->entries->isDataSaved($this->form);

        /**
         * Route our request to Craft or a third-party endpoint
         *
         * Payload forwarding is only available on front-end requests. Any
         * data saved to the database after a forwarded request is editable
         * in Craft as normal, but will not trigger any further calls to
         * the third-party endpoint.
         */
        if ($this->form->submitAction && !$request->getIsCpRequest()) {
            return $this->forwardEntrySomewhereElse($entry);
        }

        return $this->saveEntryInCraft($entry);
    }

    /**
     * @param EntryElement $entry
     *
     * @return null|Response
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \yii\web\BadRequestHttpException
     */
    private function saveEntryInCraft(Entry $entry)
    {
        $success = false;

        // Save Data and Trigger the onSaveEntryEvent
        if ($this->saveData) {
            $success = SproutForms::$app->entries->saveEntry($entry);
        } else {
            $isNewEntry = !$entry->id;

            SproutForms::$app->entries->callOnSaveEntryEvent($entry, $isNewEntry);
        }

        if (!$success) {
            return $this->redirectWithErrors($entry);
        }

        // Send Notification Emails for front-end submissions
        // @todo - Sprout Email integration
        if (!Craft::$app->getRequest()->getIsCpRequest() && $this->form->notificationEnabled) {
//            $post = $_POST;
//            SproutForms::$app->forms->sendNotification($this->form, $entry, $post);
        }

        if (Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson([
                'success' => true
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Entry saved.'));

        return $this->redirectToPostedUrl($entry);
    }

    /**
     * @param $entry
     *
     * @return null|Response
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \Twig_Error_Loader
     * @throws \yii\web\BadRequestHttpException
     */
    private function forwardEntrySomewhereElse($entry)
    {
        if (!SproutForms::$app->entries->forwardEntry($entry)) {
            return $this->redirectWithErrors($entry);
        }

        // Adds support for notification
        // @todo - Sprout Email integration
        if (!Craft::$app->getRequest()->getIsCpRequest() && $this->form->notificationEnabled) {
//            $post = $_POST;
//            SproutForms::$app->forms->sendNotification($this->form, $entry, $post);
        }

        if ($this->form->saveData) {
            $success = SproutForms::$app->entries->saveEntry($entry);

            if (!$success) {
                SproutForms::error(Craft::t('sprout-forms', 'Unable to save Form Entry to Craft.'));
            }
        }

        if (Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson([
                'success' => true
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Entry saved.'));

        return $this->redirectToPostedUrl($entry);
    }

    /**
     * Route Controller for Edit Entry Template
     *
     * @param int|null          $entryId
     * @param EntryElement|null $entry
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditEntry(int $entryId = null, EntryElement $entry = null)
    {
        if (SproutForms::$app->forms->activeCpEntry) {
            $entry = SproutForms::$app->forms->activeCpEntry;
        } else {
            if ($entry === null) {
                $entry = SproutForms::$app->entries->getEntryById($entryId);
            }

            if (!$entry) {
                throw new NotFoundHttpException(Craft::t('sprout-forms', 'Entry not found'));
            }

            Craft::$app->getContent()->populateElementContent($entry);
        }

        $form = SproutForms::$app->forms->getFormById($entry->formId);

        $saveData = SproutForms::$app->entries->isDataSaved($form);

        if (!$saveData) {
            Craft::$app->getSession()->setError(Craft::t('sprout-forms', "Unable to edit entry. Enable the 'Save Data' for this form to view, edit, or delete content."));

            return $this->renderTemplate('sprout-forms/entries');
        }

        $entryStatus = SproutForms::$app->entries->getEntryStatusById($entry->statusId);
        $statuses = SproutForms::$app->entries->getAllEntryStatuses();
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

        foreach ($fieldLayoutTabs as $tab) {
            $tabs[$tab->id]['label'] = $tab->name;
            $tabs[$tab->id]['url'] = '#tab'.$tab->sortOrder;
        }

        $variables['tabs'] = $tabs;
        $variables['fieldLayoutTabs'] = $fieldLayoutTabs;

        return $this->renderTemplate('sprout-forms/entries/_edit', $variables);
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
        $request = Craft::$app->getRequest();

        // Our EntryElement requires that we assign it a FormElement id
        $entry->formId = $this->form->id;
        $entry->ipAddress = $request->getUserIP();
        $entry->userAgent = $request->getUserAgent();

        // Set the entry attributes, defaulting to the existing values for whatever is missing from the post data
        $fieldsLocation = $request->getBodyParam('fieldsLocation', 'fields');
        $entry->setFieldValuesFromRequest($fieldsLocation);
        $entry->setFieldParamNamespace($fieldsLocation);
    }

    /**
     * Fetch or create a EntryElement class
     *
     * @return EntryElement|null
     * @throws Exception
     */
    private function getEntryModel()
    {
        $entryId = null;
        $request = Craft::$app->getRequest();

        $configSettings = Craft::$app->getConfig()->getConfigFromFile('sprout-forms');

        $enableEditFormEntryViaFrontEnd = $configSettings['enableEditFormEntryViaFrontEnd'] ?? false;

        if ($request->getIsCpRequest() || $enableEditFormEntryViaFrontEnd) {
            $entryId = $request->getBodyParam('entryId');
        }

        if (!$entryId) {
            return new EntryElement();
        }

        $entry = SproutForms::$app->entries->getEntryById($entryId);

        if (!$entry) {

            $message = Craft::t('sprout-forms', 'No form entry exists with the given ID: '.$entryId);

            throw new Exception($message);
        }

        return $entry;
    }

    /**
     * @param EntryElement $entry
     *
     * @return null|Response
     * @throws \yii\web\BadRequestHttpException
     */
    private function redirectWithErrors(Entry $entry)
    {
        SproutForms::error($entry->getErrors());

        // Send spam to the thank you page
        if (SproutForms::$app->entries->fakeIt) {
            return $this->redirectToPostedUrl($entry);
        }

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
            return $this->asJson([
                'success' => false,
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
}

<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;
use yii\web\NotFoundHttpException;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\events\OnBeforePopulateEntryEvent;

class EntriesController extends BaseController
{
    const EVENT_BEFORE_POPULATE = 'beforePopulate';

    /**
     * Allows anonymous execution
     *
     * @var string[]
     */
    protected $allowAnonymous = [
        'save-entry',
        'forward-entry',
    ];

    /**
     * @var FormElement
     */
    public $form;

    public function init()
    {
        $response = Craft::$app->getResponse();
        $headers = $response->getHeaders();
        $headers->set('Cache-Control', 'private');
    }

    /**
     * Processes form submissions
     *
     * @return void|\yii\web\Response
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveEntry()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $view = Craft::$app->getView();

        $formHandle = $request->getRequiredBodyParam('handle');
        $this->form = SproutForms::$app->forms->getFormByHandle($formHandle);

        if (!isset($this->form)) {
            throw new Exception(Craft::t('sprout-forms', 'No form exists with the handle '.$formHandle));
        }

        $event = new OnBeforePopulateEntryEvent([
            'form' => $this->form
        ]);

        $this->trigger(self::EVENT_BEFORE_POPULATE, $event);

        $entry = $this->_getEntryModel();

        Craft::$app->getContent()->populateElementContent($entry);

        $statusId = $request->getBodyParam('statusId');

        if (isset($statusId)) {
            $entry->statusId = $statusId;
        }

        // Populate the entry with post data
        $this->_populateEntryModel($entry);

        // Swap out any dynamic variables for our notifications
        if ($this->form->notificationEnabled) {
            $this->form->notificationRecipients = $view->renderObjectTemplate($this->form->notificationRecipients, $entry);
            $this->form->notificationSubject = $view->renderObjectTemplate($this->form->notificationSubject, $entry);
            $this->form->notificationSenderName = $view->renderObjectTemplate($this->form->notificationSenderName, $entry);
            $this->form->notificationSenderEmail = $view->renderObjectTemplate($this->form->notificationSenderEmail, $entry);
            $this->form->notificationReplyToEmail = $view->renderObjectTemplate($this->form->notificationReplyToEmail, $entry);
        }

        if (SproutForms::$app->entries->saveEntry($entry)) {
            // Only send notification email for front-end submissions if they are enabled
            if (!$request->getIsCpRequest() && $this->form->notificationEnabled) {
                $post = $_POST;
                SproutForms::$app->forms->sendNotification($this->form, $entry, $post);
            }

            // Removed multi-step form code on Craft3 Let's keep it clean

            if ($request->getAcceptsJson()) {
                $return['success'] = true;

                return $this->asJson($return);
            } else {
                Craft::$app->getSession()->setNotice(Craft::t('sprout-forms', 'Entry saved.'));

                return $this->redirectToPostedUrl($entry);
            }
        } else {
            return $this->_redirectOnError($entry);
        }
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

        if (!$saveData){
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
     * Verifies scenarios for error redirect
     *
     * @param EntryElement $entry
     *
     * @return void|\yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    private function _redirectOnError(EntryElement $entry)
    {
        $errors = json_encode($entry->getErrors());
        $request = Craft::$app->getRequest();
        SproutForms::error('Couldn’t save form entry. Errors: '.$errors);

        if ($request->getAcceptsJson()) {
            return $this->asJson(
                [
                    'errors' => $entry->getErrors(),
                ]
            );
        } else {
            if ($request->getIsCpRequest()) {
                // make errors available to variable
                Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'Couldn’t save entry.'));

                // Store this Entry Model in a variable in our Service layer
                // so that we can access the error object from our actionEditEntryTemplate() method
                SproutForms::$app->forms->activeCpEntry = $entry;

                // Return the form as an 'entry' variable if in the cp
                return Craft::$app->getUrlManager()->setRouteParams(
                    [
                        'entry' => $entry
                    ]
                );
            } else {
                if (SproutForms::$app->entries->fakeIt) {
                    return $this->redirectToPostedUrl($entry);
                } else {
                    Craft::$app->getSession()->setError(Craft::t('sprout-forms', 'Couldn’t save entry.'));
                    // Store this Entry Model in a variable in our Service layer
                    // so that we can access the error object from our displayForm() variable
                    SproutForms::$app->forms->activeEntries[$this->form->handle] = $entry;

                    // Return the form using it's name as a variable on the front-end
                    return Craft::$app->getUrlManager()->setRouteParams(
                        [
                            $this->form->handle => $entry
                        ]
                    );
                }
            }
        }
    }

    /**
     * Populate a EntryElement with post data
     *
     * @access private
     *
     * @param EntryElement $entry
     */
    private function _populateEntryModel(EntryElement $entry)
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
     * @access private
     * @throws Exception
     * @return EntryElement
     */
    private function _getEntryModel()
    {
        $entryId = null;
        $request = Craft::$app->getRequest();

        // Removed multi-step form code on Craft3 Let's keep it clean
        $enableEditFormEntryViaFrontEnd = false;

        if (isset(Craft::$app->getConfig()->getGeneral()->sproutForms)) {
            $sproutFormsSettings = Craft::$app->getConfig()->getGeneral()->sproutForms;
            $enableEditFormEntryViaFrontEnd = isset($sproutFormsSettings['enableEditFormEntryViaFrontEnd']) ? $sproutFormsSettings['enableEditFormEntryViaFrontEnd'] : false;
        }

        if ($request->getIsCpRequest() || $enableEditFormEntryViaFrontEnd) {
            $entryId = $request->getBodyParam('entryId');
        }

        if ($entryId) {
            $entry = SproutForms::$app->entries->getEntryById($entryId);

            if (!$entry) {
                throw new Exception(Craft::t('sprout-forms', 'No entry exists with the ID '.$entryId));
            }
        } else {
            $entry = new EntryElement();
        }

        return $entry;
    }
}

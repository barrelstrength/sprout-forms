<?php

namespace Craft;

class SproutForms_EntriesController extends BaseController
{
	/**
	 * Allows anonymous execution
	 *
	 * @var bool
	 */
	protected $allowAnonymous = array(
		'actionSaveEntry',
		'actionForwardEntry',
	);

	/**
	 * @var SproutForms_FormModel
	 */
	public $form;

	/**
	 * Forward form entry submissions to third party endpoint
	 *
	 * @throws Exception
	 * @throws HttpException
	 *
	 * @return void
	 */
	public function actionForwardEntry()
	{
		$this->requirePostRequest();

		$formHandle = craft()->request->getRequiredPost('handle');
		$this->form = sproutForms()->forms->getFormByHandle($formHandle);

		if (!isset($this->form))
		{
			throw new Exception(Craft::t('No form exists with the handle “{handle}”', array('handle' => $formHandle)));
		}

		$entry = $this->_getEntryModel();

		Craft::import('plugins.sproutforms.events.SproutForms_OnBeforePopulateEntryEvent');

		$event = new SproutForms_OnBeforePopulateEntryEvent(
			$this, array(
				'form'  => $this->form,
				'entry' => $entry
			)
		);

		sproutForms()->onBeforePopulateEntry($event);

		$entry->formId = $this->form->id;

		$this->_populateEntryModel($entry);

		// Swap out any dynamic variables for our notifications
		$this->form->notificationRecipients   = craft()->templates->renderObjectTemplate($this->form->notificationRecipients, $entry);
		$this->form->notificationSubject      = craft()->templates->renderObjectTemplate($this->form->notificationSubject, $entry);
		$this->form->notificationSenderName   = craft()->templates->renderObjectTemplate($this->form->notificationSenderName, $entry);
		$this->form->notificationSenderEmail  = craft()->templates->renderObjectTemplate($this->form->notificationSenderEmail, $entry);
		$this->form->notificationReplyToEmail = craft()->templates->renderObjectTemplate($this->form->notificationReplyToEmail, $entry);

		if (sproutForms()->entries->forwardEntry($entry))
		{
			// Adds support for notification
			if (!craft()->request->isCpRequest() && $this->form->notificationEnabled)
			{
				$post = $_POST;
				sproutForms()->forms->sendNotification($this->form, $entry, $post);
			}

			if ($this->form->saveData)
			{
				if (!sproutForms()->entries->saveEntry($entry))
				{
					SproutFormsPlugin::log("Unable to save payload data to Craft.", LogLevel::Error, true);
				}
			}

			if (craft()->request->isAjaxRequest())
			{
				$return['success'] = true;

				$this->returnJson($return);
			}
			else
			{
				craft()->userSession->setNotice(Craft::t('Entry saved.'));
				$this->redirectToPostedUrl($entry);
			}
		}
		else
		{
			$this->_redirectOnError($entry);
		}
	}

	/**
	 * Processes form submissions
	 *
	 * @throws Exception
	 * @throws HttpException
	 * @return void
	 */
	public function actionSaveEntry()
	{
		$this->requirePostRequest();

		if (craft()->request->isCpRequest())
		{
			sproutForms()->entries->userCanEditEntries();
		}

		$formHandle = craft()->request->getRequiredPost('handle');
		$this->form = sproutForms()->forms->getFormByHandle($formHandle);

		if (!isset($this->form))
		{
			throw new Exception(Craft::t('No form exists with the handle “{handle}”', array('handle' => $formHandle)));
		}

		$entry = $this->_getEntryModel();

		Craft::import('plugins.sproutforms.events.SproutForms_OnBeforePopulateEntryEvent');

		$event = new SproutForms_OnBeforePopulateEntryEvent(
			$this, array(
				'form'  => $this->form,
				'entry' => $entry
			)
		);

		craft()->sproutForms->onBeforePopulateEntry($event);

		// Our SproutForms_EntryModel requires that we assign it a SproutForms_FormModel
		$entry->formId = $this->form->id;
		$statusId      = craft()->request->getParam('statusId');

		if (isset($statusId))
		{
			$entry->statusId = $statusId;
		}

		// Populate the entry with post data
		$this->_populateEntryModel($entry);

		// Swap out any dynamic variables for our notifications
		$this->form->notificationRecipients   = craft()->templates->renderObjectTemplate($this->form->notificationRecipients, $entry);
		$this->form->notificationSubject      = craft()->templates->renderObjectTemplate($this->form->notificationSubject, $entry);
		$this->form->notificationSenderName   = craft()->templates->renderObjectTemplate($this->form->notificationSenderName, $entry);
		$this->form->notificationSenderEmail  = craft()->templates->renderObjectTemplate($this->form->notificationSenderEmail, $entry);
		$this->form->notificationReplyToEmail = craft()->templates->renderObjectTemplate($this->form->notificationReplyToEmail, $entry);

		$result   = true;
		$saveData = sproutForms()->entries->isDataSaved($this->form);

		if ($saveData)
		{
			$result = sproutForms()->entries->saveEntry($entry);
		}
		else
		{
			// call our save-entry event
			$isNewEntry = !$entry->id;
			sproutForms()->entries->callOnSaveEntryEvent($entry, $isNewEntry);
		}

		if ($result)
		{
			// Only send notification email for front-end submissions if they are enabled
			if (!craft()->request->isCpRequest() && $this->form->notificationEnabled)
			{
				$post = $_POST;
				sproutForms()->forms->sendNotification($this->form, $entry, $post);
			}

			if (!craft()->request->isCpRequest())
			{
				// Store our new entry so we can recreate the Entry object on our thank you page
				craft()->httpSession->add('lastEntryId', $entry->id);
			}

			if (craft()->request->isAjaxRequest())
			{
				$return['success'] = true;

				$this->returnJson($return);
			}
			else
			{
				craft()->userSession->setNotice(Craft::t('Entry saved.'));

				$this->redirectToPostedUrl($entry);
			}
		}
		else
		{
			// Remove our multiStepForm reference.  It will be
			// set by the template again if it needs to be.
			craft()->httpSession->remove('multiStepForm');

			$this->_redirectOnError($entry);
		}
	}

	/**
	 * Delete an entry.
	 *
	 * @return void
	 */
	public function actionDeleteEntry()
	{
		$this->requirePostRequest();
		sproutForms()->entries->userCanEditEntries();

		// Get the Entry
		$entryId = craft()->request->getRequiredPost('entryId');
		$entry   = sproutForms()->entries->getEntryById($entryId);

		// @TODO - handle errors
		$success = sproutForms()->entries->deleteEntry($entry);

		$this->redirectToPostedUrl($entry);
	}

	/**
	 * Fetch or create a SproutForms_EntryModel
	 *
	 * @access private
	 * @throws Exception
	 * @return SproutForms_EntryModel
	 */
	private function _getEntryModel()
	{
		$entryId = null;

		// If we're building our EntryModel on the front end, we have a few
		// different scenarios we need to check for:
		// 1. If entryId is included in the request, we're editing not creating a new entry
		// 2. If multiStepForm is set, we edit based on the entryId stored in session data
		if (!craft()->request->isCpRequest())
		{
			$multiStepForm        = craft()->httpSession->get('multiStepForm');
			$multiStepFormEntryId = craft()->httpSession->get('multiStepFormEntryId');

			// Check if this is a secondary step in a multiStep form
			if ($multiStepForm && $multiStepFormEntryId)
			{
				// If so, assign our $multiStepFormEntryId to be our entryId
				$entryId = $multiStepFormEntryId;
			}
			else
			{
				// @TODO - Allow using entryId in front-end forms
				// only if it is turned on in the form settings.  This should only be used
				// in secure environments where HTML cannot the submitter can be
				// identified.
				//
				// If we are explicitly modifying an existing entry, use the entryId
				// $entryId = craft()->request->getPost('entryId');
			}
		}

		$sproutFormsSettings            = craft()->config->get('sproutForms');
		$enableEditFormEntryViaFrontEnd = isset($sproutFormsSettings['enableEditFormEntryViaFrontEnd']) ? $sproutFormsSettings['enableEditFormEntryViaFrontEnd'] : false;

		if (craft()->request->isCpRequest() || $enableEditFormEntryViaFrontEnd)
		{
			$entryId = craft()->request->getPost('entryId');
		}

		if ($entryId)
		{
			$entry = sproutForms()->entries->getEntryById($entryId);

			if (!$entry)
			{
				throw new Exception(Craft::t('No entry exists with the ID “{id}”', array('id' => $entryId)));
			}
		}
		else
		{
			$entry = new SproutForms_EntryModel();
		}

		return $entry;
	}

	/**
	 * Populate a SproutForms_EntryModel with post data
	 *
	 * @access private
	 *
	 * @param SproutForms_EntryModel $entry
	 */
	private function _populateEntryModel(SproutForms_EntryModel $entry)
	{
		$entry->formId    = $this->form->id;
		$entry->ipAddress = craft()->request->getUserHostAddress();
		$entry->userAgent = craft()->request->getUserAgent();

		// Set the entry attributes, defaulting to the existing values for whatever is missing from the post data
		$fieldsLocation = craft()->request->getParam('fieldsLocation', 'fields');
		$entry->setContentFromPost($fieldsLocation);
		$entry->setContentPostLocation($fieldsLocation);
	}

	/**
	 * Route Controller for Edit Entry Template
	 *
	 * @param array $variables
	 *
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditEntryTemplate(array $variables = array())
	{
		sproutForms()->entries->userCanViewEntries();
		$entryId = craft()->request->getSegment(4);

		if (sproutForms()->forms->activeCpEntry)
		{
			$entry = sproutForms()->forms->activeCpEntry;
		}
		else
		{
			$entry = sproutForms()->entries->getEntryById($entryId);

			if (!$entry)
			{
				throw new HttpException(404);
			}
		}

		$form     = sproutForms()->forms->getFormById($entry->formId);
		$saveData = sproutForms()->entries->isDataSaved($form);

		if (!$saveData)
		{
			craft()->userSession->setError(Craft::t("Unable to edit entry. Enable the 'Save Data' for this form to view, edit, or delete content."));

			$this->renderTemplate('sproutforms/entries');
		}

		$entryStatus   = sproutForms()->entries->getEntryStatusById($entry->statusId);
		$statuses      = sproutForms()->entries->getAllEntryStatuses();
		$entryStatuses = array();

		foreach ($statuses as $key => $status)
		{
			$entryStatuses[$status->id] = $status->name;
		}

		$variables['form']        = $form;
		$variables['entryId']     = $entryId;
		$variables['entryStatus'] = $entryStatus;
		$variables['statuses']    = $entryStatuses;

		// This is our element, so we know where to get the field values
		$variables['entry'] = $entry;

		// Get the fields for this entry
		$fieldLayoutTabs = $entry->getFieldLayout()->getTabs();

		foreach ($fieldLayoutTabs as $tab)
		{
			$tabs[$tab->id]['label'] = $tab->name;
			$tabs[$tab->id]['url']   = '#tab' . $tab->sortOrder;
		}

		$variables['tabs']            = $tabs;
		$variables['fieldLayoutTabs'] = $fieldLayoutTabs;

		$this->renderTemplate('sproutforms/entries/_edit', $variables);
	}

	/**
	 * Verifies scenarios for error redirect
	 *
	 * @param SproutForms_EntryModel $entry
	 */
	private function _redirectOnError(SproutForms_EntryModel $entry)
	{
		$errors = json_encode($entry->getErrors());
		SproutFormsPlugin::log('Unable to save form entry. Errors: ' . $errors, LogLevel::Error, true);

		if (craft()->request->isAjaxRequest())
		{
			$this->returnJson(
				array(
					'errors' => $entry->getErrors(),
				)
			);
		}
		else
		{
			if (craft()->request->isCpRequest())
			{
				// make errors available to variable
				craft()->userSession->setError(Craft::t('Unable to save entry.'));

				// Store this Entry Model in a variable in our Service layer
				// so that we can access the error object from our actionEditEntryTemplate() method
				sproutForms()->forms->activeCpEntry = $entry;

				// Return the form as an 'entry' variable if in the cp
				craft()->urlManager->setRouteVariables(
					array(
						'entry' => $entry
					)
				);
			}
			else
			{
				if (sproutForms()->entries->fakeIt)
				{
					$this->redirectToPostedUrl($entry);
				}
				else
				{
					craft()->userSession->setError(Craft::t('Unable to save entry.'));
					// Store this Entry Model in a variable in our Service layer
					// so that we can access the error object from our displayForm() variable
					sproutForms()->forms->activeEntries[$this->form->handle] = $entry;

					// Return the form using it's name as a variable on the front-end
					craft()->urlManager->setRouteVariables(
						array(
							$this->form->handle => $entry
						)
					);
				}
			}
		}
	}
}

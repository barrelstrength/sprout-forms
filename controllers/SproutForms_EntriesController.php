<?php
namespace Craft;

class SproutForms_EntriesController extends BaseController
{
	/**
	 * Allows anonymous execution
	 *
	 * @var bool
	 */
	protected $allowAnonymous = array('actionSaveEntry');

	/**
	 * @var SproutForms_FormModel
	 */
	public $form;

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

		$formHandle = craft()->request->getRequiredPost('handle');
		$this->form = sproutForms()->forms->getFormByHandle($formHandle);

		if (!isset($this->form))
		{
			throw new Exception(Craft::t('No form exists with the handle “{handle}”', array('handle' => $formHandle)));
		}

		$entry = $this->_getEntryModel();

		// Our SproutForms_EntryModel requires that we assign it a SproutForms_FormModel
		$entry->formId = $this->form->id;

		// Populate the entry with post data
		$this->_populateEntryModel($entry);

		// Swap out any dynamic variables for our notifications
		$this->form->notificationRecipients   = craft()->templates->renderObjectTemplate($this->form->notificationRecipients, $entry);
		$this->form->notificationSubject      = craft()->templates->renderObjectTemplate($this->form->notificationSubject, $entry);
		$this->form->notificationSenderName   = craft()->templates->renderObjectTemplate($this->form->notificationSenderName, $entry);
		$this->form->notificationSenderEmail  = craft()->templates->renderObjectTemplate($this->form->notificationSenderEmail, $entry);
		$this->form->notificationReplyToEmail = craft()->templates->renderObjectTemplate($this->form->notificationReplyToEmail, $entry);

		if (sproutForms()->entries->saveEntry($entry))
		{
			// Only send notification email for front-end submissions if they are enabled
			if (!craft()->request->isCpRequest() && $this->form->notificationEnabled)
			{
				$this->_notifyAdmin($this->form, $entry);
			}

			// Only handle multi-page forms on the front-end
			if (!craft()->request->isCpRequest())
			{
				// Store our Entry ID for a multi-step form
				craft()->httpSession->add('multiStepFormEntryId', $entry->id);

				// Remove our multiStepForm reference. It will be
				// set by the template again if it needs to be.
				craft()->httpSession->remove('multiStepForm');

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

				$this->doSmartRedirect($entry);
			}
		}
		else
		{
			// Remove our multiStepForm reference.  It will be
			// set by the template again if it needs to be.
			craft()->httpSession->remove('multiStepForm');

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
					craft()->userSession->setError(Craft::t('Couldn’t save entry.'));

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
						$this->doSmartRedirect($entry);
					}
					else
					{
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

	/**
	 * Delete an entry.
	 *
	 * @return void
	 */
	public function actionDeleteEntry()
	{
		$this->requirePostRequest();

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

		if (craft()->request->isCpRequest())
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
	 * Notify admin
	 *
	 * @param SproutForms_FormModel  $form
	 * @param SproutForms_EntryModel $entry
	 */
	private function _notifyAdmin(SproutForms_FormModel $form, SproutForms_EntryModel $entry)
	{
		// Get our recipients
		$recipients = ArrayHelper::stringToArray($form->notificationRecipients);
		$recipients = array_unique($recipients);

		if (count($recipients))
		{
			$email                  = new EmailModel();
			$tabs                   = $form->getFieldLayout()->getTabs();
			$settings               = craft()->plugins->getPlugin('sproutforms')->getSettings();
			$templateFolderOverride = $settings->templateFolderOverride;

			$emailTemplate = craft()->path->getPluginsPath().'sproutforms/templates/_special/templates/';

			if ($templateFolderOverride)
			{
				$emailTemplateFile = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/email';

				foreach (craft()->config->get('defaultTemplateExtensions') as $extension)
				{
					if (IOHelper::fileExists($emailTemplateFile.'.'.$extension))
					{
						$emailTemplate = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/';
					}
				}
			}

			// Set our Sprout Forms Email Template path
			craft()->path->setTemplatesPath($emailTemplate);

			$email->htmlBody = craft()->templates->render(
				'email', array(
					'formName' => $form->name,
					'tabs'     => $tabs,
					'element'  => $entry
				)
			);

			craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());

			$post = (object) $_POST;

			$email->fromEmail = $form->notificationSenderEmail;
			$email->fromName  = $form->notificationSenderName;
			$email->subject   = $form->notificationSubject;

			// Has a custom subject been set for this form?
			if ($form->notificationSubject)
			{
				try
				{
					$email->subject = craft()->templates->renderObjectTemplate($form->notificationSubject, $post);
				}
				catch (\Exception $e)
				{
					SproutFormsPlugin::log($e->getMessage(), LogLevel::Error);
				}
			}

			// custom replyTo has been set for this form
			if ($form->notificationReplyToEmail)
			{
				try
				{
					$email->replyTo = craft()->templates->renderObjectTemplate($form->notificationReplyToEmail, $post);

					if (!filter_var($email->replyTo, FILTER_VALIDATE_EMAIL))
					{
						$email->replyTo = null;
					}
				}
				catch (\Exception $e)
				{
					SproutFormsPlugin::log($e->getMessage(), LogLevel::Error);
				}
			}

			foreach ($recipients as $emailAddress)
			{
				try
				{
					$email->toEmail = craft()->templates->renderObjectTemplate($emailAddress, $post);

					if (filter_var($email->toEmail, FILTER_VALIDATE_EMAIL))
					{
						craft()->email->sendEmail($email, array('sproutFormsEntry' => $entry));
					}
				}
				catch (\Exception $e)
				{
					SproutFormsPlugin::log($e->getMessage(), LogLevel::Error);
				}
			}
		}
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
		$entryId = craft()->request->getSegment(4);

		if (sproutForms()->forms->activeCpEntry)
		{
			$entry = sproutForms()->forms->activeCpEntry;
		}
		else
		{
			$entry = sproutForms()->entries->getEntryById($entryId);
		}

		$form = sproutForms()->forms->getFormById($entry->formId);

		$variables['form']    = $form;
		$variables['entryId'] = $entryId;

		// This is our element, so we know where to get the field values
		$variables['entry'] = $entry;

		// Get the fields for this entry
		$fieldLayoutTabs = $entry->getFieldLayout()->getTabs();

		foreach ($fieldLayoutTabs as $tab)
		{
			$tabs[$tab->id]['label'] = $tab->name;
			$tabs[$tab->id]['url'] = '#tab' . $tab->sortOrder;
		}

		$variables['tabs'] = $tabs;
		$variables['fieldLayoutTabs'] = $fieldLayoutTabs;

		$this->renderTemplate('sproutforms/entries/_edit', $variables);
	}

	/**
	 * Parses supported {placeholders} in redirect URL before redirecting
	 *
	 * @param SproutForms_EntryModel $entry
	 */
	public function doSmartRedirect(SproutForms_EntryModel $entry)
	{
		$vars = array_merge(
			array(
				'id'      => 0,
				'siteUrl' => craft()->getSiteUrl()
			),
			$entry->getContent()->getAttributes(),
			$entry->getAttributes()
		);

		$this->redirectToPostedUrl($vars);
	}
}
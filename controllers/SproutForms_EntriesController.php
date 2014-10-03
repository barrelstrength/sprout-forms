<?php
namespace Craft;

class SproutForms_EntriesController extends BaseController
{
	/**
	 * Allow anonymous execution
	 * 
	 * @var bool
	 */
	protected $allowAnonymous = array('actionSaveEntry');
	
	public $form;

	/**
	 * Process form submission
	 * 
	 * @return void
	 */
	public function actionSaveEntry()
	{		
		$this->requirePostRequest();

		$formHandle = craft()->request->getRequiredPost('handle');
		$this->form = craft()->sproutForms_forms->getFormByHandle($formHandle);

		craft()->content->fieldContext = $this->form->getFieldContext();
		craft()->content->contentTable = $this->form->getContentTable();

		$entry = $this->_getEntryModel();

		// Our SproutForms_EntryModel requires that we assign it a SproutForms_FormModel
		$entry->formId = $this->form->id;

		// Populate the entry with post data
		// @TODO - This function doesn't update our $entry variable, why?
		$this->_populateEntryModel($entry);

		// Swap out any dynamic variables for our notifications
		$this->form->notificationRecipients = craft()->templates->renderObjectTemplate($this->form->notificationRecipients, $entry);
		$this->form->notificationSubject = craft()->templates->renderObjectTemplate($this->form->notificationSubject, $entry);
		$this->form->notificationSenderName = craft()->templates->renderObjectTemplate($this->form->notificationSenderName, $entry);
		$this->form->notificationSenderEmail = craft()->templates->renderObjectTemplate($this->form->notificationSenderEmail, $entry);
		$this->form->notificationReplyToEmail = craft()->templates->renderObjectTemplate($this->form->notificationReplyToEmail, $entry);

		// Return the form using it's name as a variable on the front-end
		$formVariable = $this->form->handle;

		if (craft()->sproutForms_entries->saveEntry($entry)) 
		{	
			// Only send notification email for front-end submissions
			if (!craft()->request->isCpRequest()) 
			{
				$this->_notifyAdmin($this->form, $entry);
			}
			
			craft()->userSession->setNotice(Craft::t('Entry saved.'));
			
			$this->redirectToPostedUrl();
		}
		else
		{	
			// make errors available to variable
			craft()->userSession->setError(Craft::t('Couldn’t save entry.'));

			if (craft()->request->isCpRequest()) 
			{
				// Store this Entry Model in a variable in our Service layer
				// so that we can access the error object from our actionEditEntryTemplate() method
				craft()->sproutForms_forms->activeCpEntry = $entry;

				// Return the form as an 'entry' variable if in the cp
				// craft()->urlManager->setRouteVariables(array(
				// 	'entry' => $entry
				// ));
			}
			else
			{
				// Store this Entry Model in a variable in our Service layer
				// so that we can access the error object from our displayForm() variable 
				craft()->sproutForms_forms->activeEntries[$this->form->handle] = $entry;

				craft()->urlManager->setRouteVariables(array(
					$formVariable => $entry
				));
			}
		}
	}

	/**
	 * Deletes an entry.
	 * 
	 * @return void
	 */
	public function actionDeleteEntry()
	{	
		$this->requirePostRequest();
		
		// Get the Entry
		$entryId = craft()->request->getRequiredPost('entryId');
		$entry = craft()->sproutForms_entries->getEntryById($entryId);
		
		// @TODO - handle errors
		$success = craft()->sproutForms_entries->deleteEntry($entry);

		$this->redirectToPostedUrl($entry);
	}

	/**
	 * Fetches or creates an SproutForms_EntryModel.
	 *
	 * @access private
	 * @throws Exception
	 * @return SproutForms_EntryModel
	 */
	private function _getEntryModel()
	{
		$entryId = craft()->request->getPost('entryId');

		if ($entryId)
		{
			$entry = craft()->sproutForms_entries->getEntryById($entryId);

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
	 * Populates an SproutForms_EntryModel with post data.
	 *
	 * @access private
	 * @param SproutForms_EntryModel $entry
	 */
	private function _populateEntryModel(SproutForms_EntryModel $entry)
	{
		$entry->formId = $this->form->id;
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
	 * @param object $form
	 * @param object $field
	 * @return bool
	 */
	private function _notifyAdmin(SproutForms_FormModel $form, SproutForms_EntryModel $entry)
	{	
		// Get our recipients
		$recipients = explode(',', $form->notificationRecipients);
		$recipients = array_map('trim', $recipients);
		$recipients = array_unique($recipients);
		
		if (!empty($recipients)) 
		{
			$email = new EmailModel();

			// $entryCpUrl = craft()->config->get('cpTrigger') . "/sproutforms/entries/edit/" . $entry->id;

			$fields = $entry->getFieldLayout()->getFields();

			$settings = craft()->plugins->getPlugin('sproutforms')->getSettings();
			$templateFolderOverride = $settings->templateFolderOverride;

			$emailTemplate = craft()->path->getPluginsPath() . 'sproutforms/templates/_special/';

			if ($templateFolderOverride) 
			{
				$emailTemplateFile = craft()->path->getSiteTemplatesPath() . $templateFolderOverride . "/email";

				foreach (craft()->config->get('defaultTemplateExtensions') as $extension) 
				{
					if (IOHelper::fileExists($emailTemplateFile . "." . $extension)) 
					{
						$emailTemplate = craft()->path->getSiteTemplatesPath() . $templateFolderOverride . "/";
					}
				}
			}

			// Set our Sprout Forms Email Template path
			craft()->path->setTemplatesPath($emailTemplate);
			
			$email->htmlBody = craft()->templates->render('email', array(
				'formName' => $form->name,
				// 'entryCpUrl' => $entryCpUrl,
				'fields' => $fields,
				'element' => $entry
			));

			craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());

			// @TODO - create fallback text email
			// $email->body     = $form->body;

			$post = (object) $_POST;

			// Set the "from" information.
			$email->fromEmail = $form->notificationSenderEmail;
			$email->fromName  = $form->notificationSenderName;
			$email->subject   = $form->notificationSubject;

			// Has a custom subject been set for this form?
			if ($form->notificationSubject) 
			{
				try {
					$email->subject = craft()->templates->renderString($form->notificationSubject, array(
						'entry' => $post
					));
				}
				catch (\Exception $e) {
					// do nothing;  retain default subj
				}
			}
			
			// custom replyTo has been set for this form
			if ($form->notificationReplyToEmail) 
			{
				try {

					$email->replyTo = craft()->templates->renderString($form->notificationReplyToEmail, array(
						'entry' => $post
					));
					
					// we must validate this before attempting to send; 
					// invalid email will throw an error/fail to send silently
					if ( ! $this->_validEmail($email->replyTo) ) 
					{
						$email->replyTo = null;
					}
				}
				catch (\Exception $e) {
					// do nothing;  replyTo will not be included
				}
			}
			
			$error = false;
			foreach ($recipients as $emailAddress) 
			{	
				// Do we need to swap in any email addresses that 
				// were submitted with the form?
				$email->toEmail = craft()->templates->renderString($emailAddress, array(
					'entry' => $post
				));
				
				// we must validate this before attempting to send;
				// invalid email will throw an error/fail to send silently
				if ( ! $this->_validEmail($email->toEmail) ) 
				{
					continue;
				}
				
				try {
					$res = craft()->email->sendEmail($email);
				}
				catch (\Exception $e) {
					$error = true;
				}
			}

			return $error;
		}
	}
	
	/**
	 * Validate email
	 * 
	 * @param  string $email recipient list email
	 * @return bool          true/false
	 */
	private function _validEmail($email) 
	{
		return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email);
	}

	/**
	 * Edit a form.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditEntryTemplate(array $variables = array())
	{
		$entryId = craft()->request->getSegment(4);

		if (craft()->sproutForms_forms->activeCpEntry)
		{
			$entry = craft()->sproutForms_forms->activeCpEntry;
		}
		else
		{
			$entry = craft()->sproutForms_entries->getEntryById($entryId);
		}
		
		$form = craft()->sproutForms_forms->getFormById($entry->formId);

		// Set our Entry's Field Context and Content Table
		craft()->content->fieldContext = $form->getFieldContext();
		craft()->content->contentTable = $form->getContentTable();	
		
		$variables['form']    = $form;
		$variables['entryId'] = $entryId;

		// This is our element, so we know where to get the field values
		$variables['entry']   = $entry;

		// Get the fields for this entry
		$variables['fields'] = $entry->getFieldLayout()->getFields();

		$this->renderTemplate('sproutforms/entries/_edit', $variables);
	}
}
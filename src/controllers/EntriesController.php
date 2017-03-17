<?php
namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;

class EntriesController extends BaseController
{
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

		$request = Craft::$app->getRequest();
		$view    = Craft::$app->getView();
		$session = Craft::$app->getSession();

		$formHandle = $request->getRequiredBodyParam('handle');
		$this->form = SproutForms::$api->forms->getFormByHandle($formHandle);

		if (!isset($this->form))
		{
			throw new Exception(SproutForms::t('No form exists with the handle '.$formHandle);
		}

		$entry = $this->_getEntryModel();

		// Removed onBeforePopulateEntry event because not needed anymore

		$statusId = $request->getBodyParam('statusId');

		if (isset($statusId))
		{
			$entry->statusId = $statusId;
		}

		// Populate the entry with post data
		$this->_populateEntryModel($entry);

		// Swap out any dynamic variables for our notifications
		$this->form->notificationRecipients   = $view->renderObjectTemplate($this->form->notificationRecipients, $entry);
		$this->form->notificationSubject      = $view->renderObjectTemplate($this->form->notificationSubject, $entry);
		$this->form->notificationSenderName   = $view->renderObjectTemplate($this->form->notificationSenderName, $entry);
		$this->form->notificationSenderEmail  = $view->renderObjectTemplate($this->form->notificationSenderEmail, $entry);
		$this->form->notificationReplyToEmail = $view->renderObjectTemplate($this->form->notificationReplyToEmail, $entry);

		if (SproutForms::$api->entries->saveEntry($entry))
		{
			// Only send notification email for front-end submissions if they are enabled
			if (!$request->getIsCpRequest() && $this->form->notificationEnabled)
			{
				$post = $_POST;
				// @todo
				//SproutForms::$api->forms->sendNotification($this->form, $entry, $post);
			}

			// Removed multi-step form code on Craft3 Let's keep it clean

			if ($request->getIsAjax())
			{
				$return['success'] = true;

				return $this->asJson($return);
			}
			else
			{
				Craft::$app->getSession()->setNotice(SproutForms::t('Entry saved.'));

				return $this->redirectToPostedUrl($entry);
			}
		}
		else
		{
			// Remove our multiStepForm reference.  It will be
			// set by the template again if it needs to be.
			$session->remove('multiStepForm');

			return $this->_redirectOnError($entry);
		}
	}

	/**
	 * Verifies scenarios for error redirect
	 *
	 * @param EntryElement $entry
	 */
	private function _redirectOnError(EntryElement $entry)
	{
		$errors = json_encode($entry->getErrors());
		SproutForms::log("Couldn’t save form entry. Errors: ".$errors, 'error');

		if (Craft::$app->request->isAjaxRequest())
		{
			return $this->asJson(
				[
					'errors' => $entry->getErrors(),
				]
			);
		}
		else
		{
			if (Craft::$app->request->isCpRequest())
			{
				// make errors available to variable
				Craft::$app->userSession->setError(SproutForms::t('Couldn’t save entry.'));

				// Store this Entry Model in a variable in our Service layer
				// so that we can access the error object from our actionEditEntryTemplate() method
				SproutForms::$api->forms->activeCpEntry = $entry;

				// Return the form as an 'entry' variable if in the cp
				return Craft::$app->getUrlManager()->setRouteVariables(
					[
						'entry' => $entry
					]
				);
			}
			else
			{
				if (SproutForms::$api->entries->fakeIt)
				{
					return $this->redirectToPostedUrl($entry);
				}
				else
				{
					Craft::$app->getSession()->setError(SproutForms::t('Couldn’t save entry.'));
					// Store this Entry Model in a variable in our Service layer
					// so that we can access the error object from our displayForm() variable
					SproutForms::$apo->forms->activeEntries[$this->form->handle] = $entry;

					// Return the form using it's name as a variable on the front-end
					return Craft::$app->getUrlManager()->setRouteVariables(
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
		$entry->formId    = $this->form->id;
		$entry->ipAddress = $request->getUserHostAddress();
		$entry->userAgent = $request->getUserAgent();

		// Set the entry attributes, defaulting to the existing values for whatever is missing from the post data
		$fieldsLocation = $request->getBodyParam('fieldsLocation', 'fields');
		$entry->setFieldValuesFromPost($fieldsLocation);
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
		$session = Craft::$app->getSession();

		// Removed multi-step form code on Craft3 Let's keep it clean

		$sproutFormsSettings            = Craft::$app->getConfig()->get('sproutForms');
		$enableEditFormEntryViaFrontEnd = isset($sproutFormsSettings['enableEditFormEntryViaFrontEnd']) ? $sproutFormsSettings['enableEditFormEntryViaFrontEnd'] : false;

		if ($request->isCpRequest() || $enableEditFormEntryViaFrontEnd)
		{
			$entryId = $request->getPost('entryId');
		}

		if ($entryId)
		{
			$entry = SproutForms::$api->entries->getEntryById($entryId);

			if (!$entry)
			{
				throw new Exception(SproutForms::t('No entry exists with the ID '.$entryId);
			}
		}
		else
		{
			$entry = new EntryElement();
		}

		return $entry;
	}

}








?>
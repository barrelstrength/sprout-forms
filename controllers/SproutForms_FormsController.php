<?php
namespace Craft;

/**
 * Forms controller
 */
class SproutForms_FormsController extends BaseController
{
	/**
	 * Save a form
	 */
	public function actionSaveForm()
	{
		$this->requirePostRequest();

		$form = new SproutForms_FormModel();

		if(craft()->request->getPost('saveAsNew'))
		{
			$form->saveAsNew  = true;
		}
		else
		{
			$form->id        = craft()->request->getPost('id');
		}
		$form->groupId     = craft()->request->getPost('groupId');
		$form->name        = craft()->request->getPost('name');
		$form->handle      = craft()->request->getPost('handle');
		$form->titleFormat = craft()->request->getPost('titleFormat');
		$form->displaySectionTitles = craft()->request->getPost('displaySectionTitles');
		$form->redirectUri     = craft()->request->getPost('redirectUri');
		$form->submitAction    = craft()->request->getPost('submitAction');
		$form->submitButtonText     = craft()->request->getPost('submitButtonText');

		$form->notificationEnabled      = craft()->request->getPost('notificationEnabled');
		$form->notificationRecipients   = craft()->request->getPost('notificationRecipients');
		$form->notificationSubject      = craft()->request->getPost('notificationSubject');
		$form->notificationSenderName   = craft()->request->getPost('notificationSenderName');
		$form->notificationSenderEmail  = craft()->request->getPost('notificationSenderEmail');
		$form->notificationReplyToEmail = craft()->request->getPost('notificationReplyToEmail');

		// Set the field layout
		$fieldLayout =  craft()->fields->assembleLayoutFromPost();
		$fieldLayout->type = 'SproutForms_Form';
		$form->setFieldLayout($fieldLayout);

		// Delete any fields removed from the layout
		$deletedFields = craft()->request->getPost('deletedFields');

		if ($deletedFields)
		{
			// Backup our field context and content table
			$oldFieldContext = craft()->content->fieldContext;
			$oldContentTable = craft()->content->contentTable;

			// Set our field content and content table to work with our form output
			craft()->content->fieldContext = $form->getFieldContext();
			craft()->content->contentTable = $form->getContentTable();

			foreach ($deletedFields as $fieldId)
			{
				craft()->fields->deleteFieldById($fieldId);
			}

			// Reset our field context and content table to what they were previously
			craft()->content->fieldContext = $oldFieldContext;
			craft()->content->contentTable = $oldContentTable;
		}

		// Save it
		if (sproutForms()->forms->saveForm($form))
		{
			craft()->userSession->setNotice(Craft::t('Form saved.'));

			$_POST['redirect'] = str_replace('{id}', $form->id, $_POST['redirect']);
			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save form.'));

			$notificationFields = array(
				'notificationRecipients',
				'notificationSubject',
				'notificationSenderName',
				'notificationSenderEmail',
				'notificationReplyToEmail'
			);

			$notificationErrors = false;
			foreach ($form->getErrors() as $fieldHandle => $error)
			{
				if (in_array($fieldHandle, $notificationFields))
				{
					$notificationErrors = 'error';
					break;
				}
			}

			// Send the form back to the template
			craft()->urlManager->setRouteVariables(array(
				'form' => $form,
				'notificationErrors' => $notificationErrors
			));
		}
	}

	/**
	 * Edit a form.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditFormTemplate(array $variables = array())
	{
		// Immediately create a new Form
		// if (craft()->request->getSegment(3) == "new")
		// {
		// 	$form = new SproutForms_FormModel();

		// 	// Get the total number of forms we have
		// 	$totalForms = craft()->db->createCommand()
		// 		->select('count(id)')
		// 		->from('sproutforms_forms')
		// 		->queryScalar();

		// 	if ($totalForms == 0)
		// 	{
		// 		$form->name = "Form 1";
		// 		$form->handle = "form1";
		// 	}
		// 	else
		// 	{
		// 		$newFormNumber = $totalForms+1;
		// 		$form->name = "Form ".$newFormNumber;
		// 		$form->handle = "form".$newFormNumber;
		// 	}

		// 	if (sproutForms()->forms->saveForm($form))
		// 	{
		// 		$url = UrlHelper::getCpUrl('sproutforms/forms/edit/'.$form->id.'#overview');
		// 		$this->redirect($url);
		// 	}
		// 	else
		// 	{
		// 		throw new Exception(Craft::t('Error creating Form'));
		// 	}
		// }

		// Check for a Form, if we have it we have submission errors
		// and should just return to the template
		if (!isset($variables['form']))
		{
			$variables['brandNewForm'] = false;

			$variables['groups'] = sproutForms()->groups->getAllFormGroups();
			$variables['groupId'] = "";

			if (isset($variables['formId']))
			{

				// Get the Form
				$form = sproutForms()->forms->getFormById($variables['formId']);

				$variables['form'] = $form;
				$variables['title'] = $form->name;
				$variables['groupId'] = $form->groupId;

				if (!isset($variables['form']))
				{
					throw new HttpException(404);
				}

			}
			else
			{
				if (!isset($variables['form']))
				{
					$variables['form'] = new SproutForms_FormModel();
					$variables['brandNewForm'] = true;
				}

				$variables['title'] = Craft::t('Create a new form');
			}
		}

		// Set the "Continue Editing" URL
		$variables['continueEditingUrl'] = 'sproutforms/forms/edit/{id}';

		$this->renderTemplate('sproutforms/forms/_editForm', $variables);
	}

	/**
	 * Delete a form.
	 *
	 * @return void
	 */
	public function actionDeleteForm()
	{
		$this->requirePostRequest();

		// Get the Form these fields are related to
		$formId = craft()->request->getRequiredPost('id');
		$form = sproutForms()->forms->getFormById($formId);

		// @TODO - handle errors
		$success = sproutForms()->forms->deleteForm($form);

		$this->redirectToPostedUrl($form);
	}
}
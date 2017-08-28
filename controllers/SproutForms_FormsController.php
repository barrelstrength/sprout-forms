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

		if (craft()->request->isCpRequest())
		{
			sproutForms()->forms->userCanEditForms();
		}

		$form = new SproutForms_FormModel();

		if (craft()->request->getPost('saveAsNew'))
		{
			$form->saveAsNew = true;
			$duplicateForm = sproutForms()->forms->createNewForm(
				craft()->request->getPost('name'),
				craft()->request->getPost('handle')
			);

			if ($duplicateForm)
			{
				$form->id = $duplicateForm->id;
			}
			else
			{
				throw new Exception(Craft::t('Error creating Form'));
			}
		}
		else
		{
			$form->id = craft()->request->getPost('id');
		}

		$form->groupId              = craft()->request->getPost('groupId');
		$form->name                 = craft()->request->getPost('name');
		$form->handle               = craft()->request->getPost('handle');
		$form->titleFormat          = craft()->request->getPost('titleFormat');
		$form->displaySectionTitles = craft()->request->getPost('displaySectionTitles');
		$form->redirectUri          = craft()->request->getPost('redirectUri');
		$form->submitAction         = craft()->request->getPost('submitAction');
		$form->saveData             = craft()->request->getPost('saveData', 0);
		$form->submitButtonText     = craft()->request->getPost('submitButtonText');

		$form->notificationEnabled      = craft()->request->getPost('notificationEnabled');
		$form->notificationRecipients   = craft()->request->getPost('notificationRecipients');
		$form->notificationSubject      = craft()->request->getPost('notificationSubject');
		$form->notificationSenderName   = craft()->request->getPost('notificationSenderName');
		$form->notificationSenderEmail  = craft()->request->getPost('notificationSenderEmail');
		$form->notificationReplyToEmail = craft()->request->getPost('notificationReplyToEmail');
		$form->enableTemplateOverrides  = craft()->request->getPost('enableTemplateOverrides', 0);
		$form->templateOverridesFolder  = $form->enableTemplateOverrides
			? craft()->request->getPost('templateOverridesFolder')
			: null;
		$form->enableFileAttachments    = craft()->request->getPost('enableFileAttachments');

		// Set the field layout
		$fieldLayout = craft()->fields->assembleLayoutFromPost();

		if ($form->saveAsNew)
		{
			$fieldLayout = sproutForms()->fields->getDuplicateLayout($duplicateForm, $fieldLayout);
		}

		$fieldLayout->type = 'SproutForms_Form';
		$form->setFieldLayout($fieldLayout);

		// Delete any fields removed from the layout
		$deletedFields = craft()->request->getPost('deletedFields');

		if (count($deletedFields) > 0)
		{
			// Backup our field context and content table
			$oldFieldContext = craft()->content->fieldContext;
			$oldContentTable = craft()->content->contentTable;

			// Set our field content and content table to work with our form output
			craft()->content->fieldContext = $form->getFieldContext();
			craft()->content->contentTable = $form->getContentTable();

			$currentTitleFormat = null;
			foreach ($deletedFields as $fieldId)
			{
				// Each field deleted will be update the titleFormat
				$currentTitleFormat = sproutForms()->forms->cleanTitleFormat($fieldId);
				craft()->fields->deleteFieldById($fieldId);
			}

			if ($currentTitleFormat)
			{
				// update the titleFormat
				$form->titleFormat = $currentTitleFormat;
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
				'form'               => $form,
				'notificationErrors' => $notificationErrors
			));
		}
	}

	/**
	 * Edit a form.
	 *
	 * @param array $variables
	 *
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditFormTemplate(array $variables = array())
	{
		if (craft()->request->isCpRequest())
		{
			sproutForms()->forms->userCanEditForms();
		}
		// Immediately create a new Form
		if (craft()->request->getSegment(3) == "new")
		{
			$form = sproutForms()->forms->createNewForm();

			if ($form)
			{
				$url = UrlHelper::getCpUrl('sproutforms/forms/edit/' . $form->id);
				$this->redirect($url);
			}
			else
			{
				throw new Exception(Craft::t('Error creating Form'));
			}
		}
		else
		{
			if (!isset($variables['form']) && isset($variables['formId']))
			{
				$variables['brandNewForm'] = false;

				$variables['groups']  = sproutForms()->groups->getAllFormGroups();
				$variables['groupId'] = "";

				// Get the Form
				$form = sproutForms()->forms->getFormById($variables['formId']);

				if (!$form)
				{
					throw new HttpException(404);
				}

				$variables['form']    = $form;
				$variables['title']   = $form->name;
				$variables['groupId'] = $form->groupId;
			}
		}

		// Set the "Continue Editing" URL
		$variables['continueEditingUrl'] = 'sproutforms/forms/edit/{id}';

		$variables['settings'] = craft()->plugins->getPlugin('sproutforms')->getSettings();

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

		if (craft()->request->isCpRequest())
		{
			sproutForms()->forms->userCanEditForms();
		}

		// Get the Form these fields are related to
		$formId = craft()->request->getRequiredPost('id');
		$form   = sproutForms()->forms->getFormById($formId);

		// @TODO - handle errors
		$success = sproutForms()->forms->deleteForm($form);

		$this->redirectToPostedUrl($form);
	}
}
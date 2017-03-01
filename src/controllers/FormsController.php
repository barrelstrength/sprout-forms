<?php
namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\assetbundles\FormAsset;
use barrelstrength\sproutforms\elements\Form;

class FormsController extends BaseController
{
	/**
	 * Displays the form index page.
	 *
	 * @return string The rendering result
	 */
	public function actionIndex(): string
	{
		return $this->renderTemplate('sproutforms/forms/index');
	}

	/**
	 * Save a form
	 */
	public function actionSaveForm()
	{
		$this->requirePostRequest();

		$form = new Form();

		if (Craft::$app->getRequest()->getBodyParam('saveAsNew'))
		{
			$form->saveAsNew = true;
			$duplicateForm = SproutForms::$api()->forms->createNewForm(
				Craft::$app->getRequest()->getBodyParam('name'),
				Craft::$app->getRequest()->getBodyParam('handle')
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
			$form->id = Craft::$app->getRequest()->getBodyParam('id');
		}

		$form->groupId              = Craft::$app->getRequest()->getBodyParam('groupId');
		$form->name                 = Craft::$app->getRequest()->getBodyParam('name');
		$form->handle               = Craft::$app->getRequest()->getBodyParam('handle');
		$form->titleFormat          = Craft::$app->getRequest()->getBodyParam('titleFormat');
		$form->displaySectionTitles = Craft::$app->getRequest()->getBodyParam('displaySectionTitles');
		$form->redirectUri          = Craft::$app->getRequest()->getBodyParam('redirectUri');
		$form->submitAction         = Craft::$app->getRequest()->getBodyParam('submitAction');
		$form->savePayload          = Craft::$app->getRequest()->getBodyParam('savePayload', 0);
		$form->submitButtonText     = Craft::$app->getRequest()->getBodyParam('submitButtonText');

		$form->notificationEnabled      = Craft::$app->getRequest()->getBodyParam('notificationEnabled');
		$form->notificationRecipients   = Craft::$app->getRequest()->getBodyParam('notificationRecipients');
		$form->notificationSubject      = Craft::$app->getRequest()->getBodyParam('notificationSubject');
		$form->notificationSenderName   = Craft::$app->getRequest()->getBodyParam('notificationSenderName');
		$form->notificationSenderEmail  = Craft::$app->getRequest()->getBodyParam('notificationSenderEmail');
		$form->notificationReplyToEmail = Craft::$app->getRequest()->getBodyParam('notificationReplyToEmail');
		$form->enableTemplateOverrides  = Craft::$app->getRequest()->getBodyParam('enableTemplateOverrides', 0);
		$form->templateOverridesFolder  = $form->enableTemplateOverrides
			? Craft::$app->getRequest()->getBodyParam('templateOverridesFolder')
			: null;
		$form->enableFileAttachments    = Craft::$app->getRequest()->getBodyParam('enableFileAttachments');

		// Set the field layout
		$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

		if ($form->saveAsNew)
		{
			$fieldLayout = SproutForms::$api->fields->getDuplicateLayout($duplicateForm, $fieldLayout);
		}

		$fieldLayout->type = Form::class;
		$form->setFieldLayout($fieldLayout);

		// Delete any fields removed from the layout
		$deletedFields = Craft::$app->getRequest()->getBodyParam('deletedFields');

		if (count($deletedFields) > 0)
		{
			// Backup our field context and content table
			$oldFieldContext = Craft::$app->content->fieldContext;
			$oldContentTable = Craft::$app->content->contentTable;

			// Set our field content and content table to work with our form output
			Craft::$app->content->fieldContext = $form->getFieldContext();
			Craft::$app->content->contentTable = $form->getContentTable();

			$currentTitleFormat = null;

			foreach ($deletedFields as $fieldId)
			{
				// Each field deleted will be update the titleFormat
				$currentTitleFormat = SproutForms::$api->forms->cleanTitleFormat($fieldId);
				Craft::$app->fields->deleteFieldById($fieldId);
			}

			if ($currentTitleFormat)
			{
				// update the titleFormat
				$form->titleFormat = $currentTitleFormat;
			}

			// Reset our field context and content table to what they were previously
			Craft::$app->content->fieldContext = $oldFieldContext;
			Craft::$app->content->contentTable = $oldContentTable;
		}

		// Save it
		if (SproutForms::$api->forms->saveForm($form))
		{
			Craft::$app->userSession->setNotice(SproutForms::t('Form saved.'));

			$_POST['redirect'] = str_replace('{id}', $form->id, $_POST['redirect']);
			return $this->redirectToPostedUrl();
		}
		else
		{
			Craft::$app->userSession->setError(SproutForms::t('Couldn’t save form.'));

			$notificationFields = [
				'notificationRecipients',
				'notificationSubject',
				'notificationSenderName',
				'notificationSenderEmail',
				'notificationReplyToEmail'
			];

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
			Craft::$app->getUrlManager()->setRouteParams([
					'form'               => $form,
					'notificationErrors' => $notificationErrors
				]
			);
		}
	}

	/**
	 * Edit a form.
	 *
	 * @param int|null  $formId The form's ID, if editing an existing form.
	 *
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditFormTemplate(int $formId = null): string
	{
		// Immediately create a new Form
		if (Craft::$app->request->getSegment(3) == "new")
		{
			$form = SproutForms::$api->forms->createNewForm();

			if ($form)
			{
				$url = UrlHelper::cpUrl('sproutforms/forms/edit/' . $form->id);
				return $this->redirect($url);
			}
			else
			{
				throw new Exception(Craft::t('Error creating Form'));
			}
		}
		else
		{
			if ($formId)
			{
				$variables['brandNewForm'] = false;

				$variables['groups']  = SproutForms::$api->groups->getAllFormGroups();
				$variables['groupId'] = "";

				// Get the Form
				$form = SproutForms::$api->forms->getFormById($formId);

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

		$variables['settings'] = Craft::$app->plugins->getPlugin('sproutforms')->getSettings();

		return $this->renderTemplate('sproutforms/forms/_editForm', $variables);
	}
}

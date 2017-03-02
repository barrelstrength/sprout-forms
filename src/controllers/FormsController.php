<?php
namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\assetbundles\FormAsset;
use barrelstrength\sproutforms\elements\Form as FormElement;

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

		$request = Craft::$app->getRequest();
		$form    = new FormElement();

		if ($request->getBodyParam('saveAsNew'))
		{
			$form->saveAsNew = true;
			$duplicateForm = SproutForms::$api()->forms->createNewForm(
				$request->getBodyParam('name'),
				$request->getBodyParam('handle')
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
			$form->id = $request->getBodyParam('id');
		}

		$form->groupId              = $request->getBodyParam('groupId');
		$form->name                 = $request->getBodyParam('name');
		$form->handle               = $request->getBodyParam('handle');
		$form->titleFormat          = $request->getBodyParam('titleFormat');
		$form->displaySectionTitles = $request->getBodyParam('displaySectionTitles');
		$form->redirectUri          = $request->getBodyParam('redirectUri');
		$form->submitAction         = $request->getBodyParam('submitAction');
		$form->savePayload          = $request->getBodyParam('savePayload', 0);
		$form->submitButtonText     = $request->getBodyParam('submitButtonText');

		$form->notificationEnabled      = $request->getBodyParam('notificationEnabled');
		$form->notificationRecipients   = $request->getBodyParam('notificationRecipients');
		$form->notificationSubject      = $request->getBodyParam('notificationSubject');
		$form->notificationSenderName   = $request->getBodyParam('notificationSenderName');
		$form->notificationSenderEmail  = $request->getBodyParam('notificationSenderEmail');
		$form->notificationReplyToEmail = $request->getBodyParam('notificationReplyToEmail');
		$form->enableTemplateOverrides  = $request->getBodyParam('enableTemplateOverrides', 0);
		$form->templateOverridesFolder  = $form->enableTemplateOverrides
			? $request->getBodyParam('templateOverridesFolder')
			: null;
		$form->enableFileAttachments    = $request->getBodyParam('enableFileAttachments');

		// Set the field layout
		$fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();

		if ($form->saveAsNew)
		{
			$fieldLayout = SproutForms::$api->fields->getDuplicateLayout($duplicateForm, $fieldLayout);
		}

		$fieldLayout->type = Form::class;
		$form->setFieldLayout($fieldLayout);

		// Delete any fields removed from the layout
		$deletedFields = $request->getBodyParam('deletedFields');

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
		if (!SproutForms::$api->forms->saveForm($form))
		{
			Craft::$app->getSession()->setError(SproutForms::t('Couldn’t save form.'));

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

			//@todo - error for some reason the $form.getErrors() is not
			//passing to the view. either the variable form or notificationErrors
			//Craft::dd($form->getErrors());

			// Send the form back to the template
			Craft::$app->getUrlManager()->setRouteParams([
					'form'               => $form,
					'notificationErrors' => $notificationErrors
				]
			);

			return null;
		}

		Craft::$app->getSession()->setNotice(SproutForms::t('Form saved.'));

		#$_POST['redirect'] = str_replace('{id}', $form->id, $_POST['redirect']);

		return $this->redirectToPostedUrl($form);
	}

	/**
	 * Edit a form.
	 *
	 * @param int|null  $formId The form's ID, if editing an existing form.
	 *
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditFormTemplate(int $formId = null)
	{
		// Immediately create a new Form
		if (Craft::$app->request->getSegment(3) == "new")
		{
			$form = SproutForms::$api->forms->createNewForm();

			if ($form)
			{
				$url = UrlHelper::cpUrl('sprout-forms/forms/edit/' . $form->id);
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
		$variables['continueEditingUrl'] = 'sprout-forms/forms/edit/{id}';

		$variables['settings'] = Craft::$app->plugins->getPlugin('sproutforms')->getSettings();

		return $this->renderTemplate('sproutforms/forms/_editForm', $variables);
	}

	/**
	 * Delete a form.
	 *
	 * @return void
	 */
	public function actionDeleteForm()
	{
		$this->requirePostRequest();

		$request = Craft::$app->getRequest();

		// Get the Form these fields are related to
		$formId = $request->getRequiredBodyParam('id');
		$form   = SproutForms::$api->forms->getFormById($formId);

		// @TODO - handle errors
		$success = SproutForms::$api->forms->deleteForm($form);

		return $this->redirectToPostedUrl($form);
	}
}

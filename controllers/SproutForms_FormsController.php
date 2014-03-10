<?php
namespace Craft;

class SproutForms_FormsController extends BaseController
{

	/**
	 * Saves a form.
	 * 
	 * @return void
	 */
	public function actionSaveForm()
	{
		$this->requirePostRequest();

		$form = new SproutForms_FormModel();

		$form->id           = craft()->request->getPost('id');		
		$form->name         = craft()->request->getPost('name');
		$form->handle       = craft()->request->getPost('handle');
		$form->redirectUri  = craft()->request->getPost('redirectUri');
		$form->submitButtonType = craft()->request->getPost('submitButtonType');
		$form->submitButtonText  = craft()->request->getPost('submitButtonText');
		$form->notification_subject  = craft()->request->getPost('notification_subject');
		$form->notification_reply_to  = craft()->request->getPost('notification_reply_to');

		if (craft()->sproutForms->saveForm($form))
		{
			craft()->userSession->setNotice(Craft::t('Form saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save form.'));

			// Send the field back to the template
			craft()->urlManager->setRouteVariables(array(
				'form' => $form
			));
		}

	}
    
    /**
     * Deletes a field.
     * 
     * @return void
     */
    public function actionDeleteForm()
    {
    	$this->requirePostRequest();
    	$this->requireAjaxRequest();
    	
    	$success = craft()->sproutForms->deleteForm(craft()->request->getRequiredPost('id'));
    	$this->returnJson(array('success' => $success));
    }
    
    /**
     * Update notifications
     * 
     * @return void
     */
    public function actionUpdateNotifications()
    {
		$this->requirePostRequest();

		$form = craft()->sproutForms->getFormById(craft()->request->getPost('id'));
		$form->email_distribution_list = craft()->request->getPost('email_distribution_list');
		$form->notification_subject = craft()->request->getPost('notification_subject');
		$form->notification_reply_to = craft()->request->getPost('notification_reply_to');

		if (craft()->sproutForms->saveForm($form))
		{
			craft()->userSession->setNotice(Craft::t('Changes saved.'));

			$this->redirectToPostedUrl(array(
				'id' => $form->id,
			));
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save changes.'));
		}

		// Send the field back to the template
		craft()->urlManager->setRouteVariables(array(
			'form' => $form
		));
    }
}
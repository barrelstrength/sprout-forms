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

		if (craft()->sproutForms->saveForm($form))
		{
			craft()->userSession->setNotice(Craft::t('Form saved.'));

			$this->redirectToPostedUrl(array(

			));
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save form.'));
		}

		// Send the field back to the template
		craft()->urlManager->setRouteVariables(array(
			'form' => $form
		));
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
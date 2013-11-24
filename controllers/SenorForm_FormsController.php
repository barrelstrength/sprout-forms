<?php
namespace Craft;

class SenorForm_FormsController extends BaseController
{

	/**
	 * Saves a form.
	 */
	public function actionSaveForm()
	{
		$this->requirePostRequest();

		$form = new SenorForm_FormModel();

		$form->id           = craft()->request->getPost('id');		
		$form->name         = craft()->request->getPost('name');
		$form->handle       = craft()->request->getPost('handle');

		if (craft()->senorForm->saveForm($form))
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
     */
    public function actionDeleteForm()
    {
    	$this->requirePostRequest();
    	$this->requireAjaxRequest();
    	
    	$success = craft()->senorForm->deleteForm(craft()->request->getRequiredPost('id'));
    	$this->returnJson(array('success' => $success));
    }
    
    public function actionUpdateNotifications()
    {
		$this->requirePostRequest();

		$form = craft()->senorForm->getFormById(craft()->request->getPost('id'));
		$form->email_distribution_list = craft()->request->getPost('email_distribution_list');

		if (craft()->senorForm->saveForm($form))
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
			'field' => $form
		));
    }

}
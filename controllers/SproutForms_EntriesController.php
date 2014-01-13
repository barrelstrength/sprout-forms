<?php
namespace Craft;

class SproutForms_EntriesController extends BaseController
{
	/**
	 * Allow anonymous execution
	 * 
	 * @var bool
	 */
	public $allowAnonymous = true;

	/**
	 * Process form submission
	 * 
	 * @return void
	 */
	public function actionSaveEntry()
	{		
		// pre $_POST processing hook
		craft()->plugins->call('sproutFormsPrePost');
		
		// if no $_POST, throws 400
		$this->requirePostRequest();

		// get form w/ fields
		if ( ! $formRecord = SproutForms_FormRecord::model()
		->with('field')
		->find('t.handle=:handle', array(':handle' => craft()->request->getPost('handle'))))
		{
			craft()->user->setFlash('error', Craft::t('Error retrieving form.'));
			$this->redirectToPostedUrl();
		}

		// Don't worry about these fields when we append our field namespaces
		$adminFields = array('action', 'redirect', 'handle');
		
		// These will be the fields we'll want to validate & save
		$fieldsToSave = array();
		
		foreach (craft()->request->getPost() as $key => $value) 
		{			
			if ( ! in_array($key, $adminFields) )
			{				
				// append field namespace
				if ( ! preg_match('/^formId\d+_/', $key) )
				{
					$fieldsToSave['formId' . $formRecord->id . '_' . $key] = $value;
					$_POST['formId' . $formRecord->id . '_' . $key] = $value;
				}
			}
		}

		$contentRecord = new SproutForms_ContentRecord();
		
		foreach ($contentRecord->attributes as $column => $value)
		{
			// process only the field was submitted
			$field = isset($fieldsToSave[$column]) ? $fieldsToSave[$column] : null;
			if ($field)
			{
				if (is_array($field))
				{
					$field = json_encode($field);
				}								
				$contentRecord->$column = $field;
			}
		}

		$contentRecord->formId = $formRecord->id;
		$contentRecord->_setRules($fieldsToSave);
		
		

		if ($contentRecord->save())
		{
			// Send an email with the form information
			$this->_notifyAdmin($formRecord, craft()->sproutForms->getEntryById($contentRecord->id));

	    	craft()->user->setFlash('notice', Craft::t('Form successfully submitted.'));
		    $this->redirectToPostedUrl();
		}
		else 
		{		


			// @TODO - Since we are namespacing our fields, we can't easily 
			// edit the private Errors messages in our form model so we rebuild 
			// our error messages here and strip out the reference they have to 
			// their form IDs.  This limits how our errors can be accessed in 
			// our templates to the 'errors' object and doesn't allow a user
			// to use the functions associated with the record/model but it
			// gets us the right messages until we can find a better way to 
			// handle this
			$errors = array();
			$formIdNamespaceVariable = "formId" . $contentRecord->formId . "_";
			$formIdNamespaceMessage = "Form Id" . $contentRecord->formId . " ";

			foreach ($contentRecord->errors as $key => $errorArray) {

				// $key = str_replace($formIdNamespaceVariable, "", $key);

				foreach ($errorArray as $_key => $error) {
					$error = str_replace($formIdNamespaceMessage, "", $error);					
					$errorArray[$_key] = $error;
				}
				$errors[$key] = $errorArray;
				
			}
						
			// make errors available to variable
			craft()->user->setFlash('error', Craft::t('Error submitting form.'));
			craft()->user->setFlash('errors', $errors);
			
			// make errors available to template
			craft()->urlManager->setRouteVariables(array(
				'error' => Craft::t('Error submitting form.'),
				'errors' => $errors,
				'entry' => craft()->request->getPost()
			));			
		}
	}

	/**
	 * Notify admin
	 * 
	 * @param object $formRecord
	 * @param object $contentRecord
	 * @return bool
	 */
	private function _notifyAdmin($formRecord = FALSE, $contentRecord = FALSE)
	{
		if ( ! $formRecord || ! $contentRecord)
		{
			return FALSE;
		}

		// notify if distribution list is set up
		$distro_list = array_unique(array_filter(explode(',', $formRecord->email_distribution_list)));
		if ( ! empty($distro_list))
		{
			// prep data for view
			$data = array();

			foreach ($contentRecord->form->field as $k=>$v)
			{
				$data[$v->name] = nl2br($v->getContent()); // new lines to <br/>
			}
			
			$email = new EmailModel();
      		$email->htmlBody = craft()->templates->render('sproutforms/emails/default', array(
				'data' => $data, 
				'form' => $formRecord->name,
				'viewFormEntryUrl' => craft()->config->get('cpTrigger') . "/sproutforms/edit/" . $formRecord->id . "#tab-entries"
			));
			$email->subject = 'A form has been submitted on your website';
			$email->htmlBody = html_entity_decode($email->htmlBody); // mainly for <br/>

			$error = false;
			foreach ($distro_list as $email_address)
			{
				try
				{
					$email->toEmail = trim($email_address);
					$res = craft()->email->sendEmail($email);
				}
				catch(\Exception $e)
				{
					$error = true;				
				}
			}
			return $error;
		}
	}
}
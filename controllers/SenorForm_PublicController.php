<?php
namespace Craft;

/**
 * 
 * @author zig
 *
 */
class SenorForm_PublicController extends BaseController
{
	/**
	 * Allow anonymous execution
	 * @var bool
	 */
	public $allowAnonymous = true;

	/**
	 * Process form submission
	 * @return void
	 */
	public function actionPost()
	{		
		// pre post processing hook
		craft()->plugins->call('senorformPrePost');
		
		// if no $_POST, throws 400
		$this->requirePostRequest();

		// get form w/ fields
		if ( ! $formRecord = SenorForm_FormRecord::model()
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
			if ( ! in_array($key, $adminFields))
			{				
				// append field namespace
				if ( ! preg_match('/^formId\d+_/', $key))
				{
					$fieldsToSave['formId' . $formRecord->id . '_' . $key] = $value;
					$_POST['formId' . $formRecord->id . '_' . $key] = $value;
				}
			}
		}

		$contentRecord = new SenorForm_ContentRecord();
		
		foreach ($contentRecord->attributes as $column => $value)
		{
			// process only the field was submitted
			$field = isset($fieldsToSave[$column]) ? $fieldsToSave[$column] : null;
			if ($field)
			{
				if (is_array($field))
				{
					// we need to get the options and drill down
					$fieldRecord = craft()->senorForm_field->getFieldByHandle($column);
					
					$multiField = array();
					foreach ($post as $option_key => $option_value)
					{
						$multiField[$fieldRecord->settings['options'][$option_key]['label']] = $option_value;
					}
					$field = json_encode($multiField);
				}								
				$contentRecord->$column = $field;
			}
		}

		$contentRecord->formId = $formRecord->id;
		$contentRecord->_setRules($fieldsToSave);

		// @TODO - the else statement needs some love.
		if ($contentRecord->save())
		{
			// Send an email with the form information
			// @TODO - clean this up and integrate this better
			//$this->_notifyAdmin($formRecord, craft()->senorForm->getEntryById($contentRecord->id));

	    	craft()->user->setFlash('notice', Craft::t('Form successfully submitted.'));
		    $this->redirectToPostedUrl();
		}
		else 
		{
			echo "<pre>";
			print_r($contentRecord->errors);
			echo "</pre>";
			die('fin');
			
			// make errors available to variable
			craft()->user->setFlash('error', Craft::t('Error submitting form.'));
			craft()->user->setFlash('errors', $contentRecord->errors);

			// make errors available to template
			craft()->urlManager->setRouteVariables(array(
				'error' => Craft::t('Error submitting form.'),
				'errors' => $contentRecord->errors
			));			
		}
	}

	/**
	 * Notify admin
	 * @param object $formRecord
	 * @param object $contentRecord
	 */
	private function _notifyAdmin($formRecord = FALSE, $contentRecord = FALSE)
	{
		return true;
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
				$data[$v->name] = $v->getContent();
			}
			
			$email = new EmailModel();
      		$email->htmlBody = craft()->templates->render('senorform/emails/default', array(
				'data' => $data, 
				'form' => $formRecord->name,
				'viewFormEntryUrl' => craft()->config->get('cpTrigger') . "/senorform/edit/" . $formRecord->id . "#tab-entries"
			));
      		
			$email->subject = 'A form has been submitted on your website';

			foreach ($distro_list as $email_address)
			{
				try
				{
					$email->toEmail = trim($email_address);
					$res = craft()->email->sendEmail($email);
				}
				catch(\Exception $e)
				{
					// TODO: handle error					
				}
			}
		}
	}

}
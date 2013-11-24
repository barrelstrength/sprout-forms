<?php
namespace Craft;

class SenorForm_PublicController extends BaseController
{

	public $allowAnonymous = true;

	/**
	 * Saves a field.
	 */
	public function actionPost()
	{
		// pre post processing hook
		$result = craft()->plugins->call('verifyCaptchaSubmission');

		if ( $result['InvisibleCaptcha'] ) {
			// if you did not include an onSuccessRedirect <input>
			unset($_POST['chp']);
		} else {
			// if you did not include an onFailureRedirect <input>
			// do something...
		}

		// if no $_POST, throws 400
		$this->requirePostRequest();

		// get form w/ fields
		if( ! $formRecord = SenorForm_FormRecord::model()
		->with('field')
		->find('t.handle=:handle', array(':handle' => craft()->request->getPost('handle'))))
		{
			\Yii::app()->user->setFlash('error', Craft::t('Error retrieving form.'));
			$this->redirectToPostedUrl();
		}

		// Don't worry about these fields when we append our field namespaces
		$adminFields = array('action', 'redirect', 'handle');

		foreach (\Yii::app()->request->getPost() as $key => $value)
		{
			if ( ! in_array($key, $adminFields))
			{

				// See if our field namespace exists, and prepend it if it doesn't
				// @TODO - rather than having some fields with the namespace and some without
				// we may want to update the tags so all the fields don't use a namespace
				// by default and we just handle it in the code.

				$pattern = '/^formId\d+_/';

				if ( ! preg_match($pattern, $key))
				{
						$fieldName = 'formId' . $formRecord->id . '_' . $key;
						$_POST[$fieldName] = $value;
						unset($_POST[$key]);
				}
			}
		}

		// set content
		$contentRecord = new SenorForm_ContentRecord();

		foreach($contentRecord->attributes as $column => $value)
		{
			if($post = craft()->request->getPost($column))
			{
				if(is_array($post))
				{
					// we need to get the options and drill down
					$fieldRecord = craft()->senorForm_field->getFieldByHandle($column);

					$to_save = array();
					foreach($post as $option_key => $option_value)
					{
						$to_save[$fieldRecord->settings['options'][$option_key]['label']] = $option_value;
					}
					$post = json_encode($to_save);
				}

				$contentRecord->$column = $post;
			}
		}

		$contentRecord->formId = $formRecord->id;
		$contentRecord->_setRules();


		// @TODO - the else statement needs some love.
		if($contentRecord->save())
		{
				// Send an email with the form information
				// @TODO - clean this up and integrate this better
				$this->_notifyAdmin($formRecord, craft()->senorForm->getEntryById($contentRecord->id));

	    	\Yii::app()->user->setFlash('notice', Craft::t('Form successfully submitted.'));
		    $this->redirectToPostedUrl();
		}
		else
		{

			$values = array();
			// Update our attributes they don't include our namespace
			foreach ($contentRecord->getAttributes() as $handleRaw => $value) {

				// cleanup the handle
				$pattern = '/^formId\d+_/';
				$handle = preg_split($pattern, $handleRaw);

				if (isset($handle[1]))
				{
					$handle = $handle[1];

					// @TODO - we can't update our object with new attribute names because they validate against the model and
					// the model doesn't allow us to add non-namspaced keys for our attributes .  This is a hacky way to just
					// get us an array.
					$values[$handle] = $value;

				}
			}

			$errors = array();

			// Update our error messages and variables so they don't include our namespace
			foreach ($contentRecord->errors as $handleRaw => $errorMessages)
			{

				// cleanup the handle
				$pattern = '/^formId\d+_/';
				$handle = preg_split($pattern, $handleRaw);
				$handle = $handle[1];

				$i = 0;
				foreach ($errorMessages as $key => $messageRaw) {
					// cleanup the message
					$pattern2 = '/^Form Id\d+ /';
					$message = preg_split($pattern2, $messageRaw);
					$message = $message[1];

					$errors[$handle][$i] = $message;

					$contentRecord->addError($handle, $message);
					$contentRecord->clearErrors($handleRaw);
					$i++;
				}
			}

			\Yii::app()->user->setFlash('error', Craft::t('Error submitting form.'));

			// Send the account back to the template
			craft()->urlManager->setRouteVariables(array(
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
		if( ! $formRecord || ! $contentRecord)
		{
			return FALSE;
		}
		
		// notify if distribution list is set up
		$distro_list = array_unique(array_filter(explode(',', $formRecord->email_distribution_list)));

		if( ! empty($distro_list))
		{
			// prep data for view
			$data = array();

			foreach($contentRecord->form->field as $k=>$v)
			{
				$data[$v->name] = $v->getContent();
			}

			$email = new EmailModel();

      	$email->htmlBody = craft()->templates->render('senorform/emails/default', array(
					'data' => $data,
					'form' => $formRecord->name,
					'viewFormEntryUrl' => craft()->config->get('cpTrigger') . "/senorform/forms/edit/" . $formRecord->id . "#tab-entries"
			));

      // @TODO - Think through this a bit more, will 
      // this always be the way the email field is used?
      // Maybe we should give the user control over this in some way using 
      // a form field name variable that we replace, i.e.: 
      // Reply To Address:	{email}
      if (isset($data['Email']) && $data['Email'] != "")
      {
      	$email->replyTo = $data['Email'];	
      }      

      // @TODO - make this dynamic
			$email->subject = 'A form has been submitted on the ' . craft()->siteName . ' website';

			foreach($distro_list as $email_address)
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

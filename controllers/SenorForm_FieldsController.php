<?php
namespace Craft;

class SenorForm_FieldsController extends BaseController
{


	/**
	 * Saves a field.
	 */
	public function actionSaveField()
	{
		$this->requirePostRequest();

		$field = new SenorForm_FieldModel();
		$field->formId       = craft()->request->getRequiredPost('formId');
		$field->id           = craft()->request->getPost('fieldId');		
		$field->name         = craft()->request->getPost('name');		
		$field->handle       = "formId" . $field->formId . "_" . craft()->request->getPost('handle'); // Append our FormId on the from of our field name
		$field->instructions = craft()->request->getPost('instructions');
		$field->translatable = (bool) craft()->request->getPost('translatable');
		$field->type         = craft()->request->getRequiredPost('type');

		$typeSettings = craft()->request->getPost('types');
		if (isset($typeSettings[$field->type]))
		{
			$field->settings = $typeSettings[$field->type];
		}
		
		$field->validation = ''; // reset
		if( $validation = craft()->request->getPost('validation'))
		{
			if($validation == '*')
			{
				$field->validation = implode(',', craft()->senorForm_field->getValidationOptions());
			}
			else 
			{
				$field->validation = implode(',', $validation);
			}
		}		

		if (craft()->senorForm_field->saveField($field))
		{
			craft()->userSession->setNotice(Craft::t('Field saved.'));

			$this->redirectToPostedUrl(array(
				'fieldId' => $field->id,
				'formId' => $field->formId,
			));
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save field.'));
		}

		// Send the field back to the template
		craft()->urlManager->setRouteVariables(array(
			'field' => $field
		));
	}

	/**
	 * Deletes a field.
	 */
	public function actionDeleteField()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$fieldId = craft()->request->getRequiredPost('id');
		$success = craft()->senorForm_field->deleteField($fieldId);
		$this->returnJson(array('success' => $success));
	}

}
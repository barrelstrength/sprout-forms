<?php
namespace Craft;

class SproutForms_FieldsController extends BaseController
{
	/**
	 * Save a field.
	 */
	public function actionSaveField()
	{
		$this->requirePostRequest();

		// Get the Form these fields are related to
		$formId = craft()->request->getRequiredPost('formId');
		$form = craft()->sproutForms_forms->getFormById($formId);
		
		$field = new FieldModel();
		
		$field->id           = craft()->request->getPost('fieldId');
		$field->name         = craft()->request->getPost('name');
		$field->handle       = craft()->request->getPost('handle');
		$field->instructions = craft()->request->getPost('instructions');
		$field->required     = craft()->request->getPost('required');
		$field->translatable = (bool) craft()->request->getPost('translatable');

		$field->type = craft()->request->getRequiredPost('type');

		$typeSettings = craft()->request->getPost('types');

		if (isset($typeSettings[$field->type]))
		{
			$field->settings = $typeSettings[$field->type];
		}

		if (craft()->sproutForms_fields->saveField($form, $field)) 
		{
			craft()->userSession->setNotice(Craft::t('Field saved.'));

			$this->redirectToPostedUrl($field);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save field.'));

			// Send the field back to the template
			craft()->urlManager->setRouteVariables(array(
				'field' => $field
			)); 
		}
	}

	/**
	 * Edit a field.
	 *
	 * @param array $variables
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditFieldTemplate(array $variables = array())
	{
		$formId = craft()->request->getSegment(3);
		$form = craft()->sproutForms_forms->getFormById($formId);

		if (!empty($variables['fieldId']))
		{
			if (empty($variables['field']))
			{
				$field = craft()->fields->getFieldById($variables['fieldId']);
				$variables['field'] = $field;
	
				$fieldLayoutField = FieldLayoutFieldRecord::model()->find(array(
					'condition' => 'fieldId = :fieldId AND layoutId = :layoutId',
					'params' => array(':fieldId' => $field->id, ':layoutId' => $form->fieldLayoutId)
				));
				
				$variables['required'] = $fieldLayoutField->required;
				
				if (!$variables['field'])
				{
					throw new HttpException(404);
				}
			}

			$variables['title'] = (isset($field->name) ? $field->name : "");
		}
		else
		{
			
			if (empty($variables['field']))
			{
				$variables['field'] = new FieldModel();
			}

			$variables['title'] = Craft::t('Create a new field');
		}

		$this->renderTemplate('sproutforms/forms/fields/_edit', $variables);
	}

	/**
	 * Deletes a field.
	 * 
	 * @return void
	 */
	public function actionDeleteField()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();
		
		$fieldId = craft()->request->getRequiredPost('id');
		$success = craft()->fields->deleteFieldById($fieldId);
		$this->returnJson(array(
			'success' => $success
		));
	}
	
	/**
	 * Reorder a field
	 * 
	 * @return json
	 */
	public function actionReorderFields()
	{
		craft()->userSession->requireAdmin();
		$this->requirePostRequest();
		$this->requireAjaxRequest();
		
		$fieldIds = JsonHelper::decode(craft()->request->getRequiredPost('ids'));
		craft()->sproutForms_fields->reorderFields($fieldIds);
	
		$this->returnJson(array(
			'success' => true
		));
	}
}
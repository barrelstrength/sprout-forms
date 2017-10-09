<?php
namespace Craft;

class SproutForms_FieldsController extends BaseController
{
	/**
	 * This action allows to load the modal field template.
	 *
	 */
	public function actionModalField()
	{
		$this->requireAjaxRequest();
		$formId = craft()->request->getRequiredParam('formId');
		$form   = sproutForms()->forms->getFormById($formId);

		$this->returnJson(sproutForms()->fields->getModalFieldTemplate($form));
	}

	/**
	 * Save a field.
	 */
	public function actionSaveField()
	{
		$this->requirePostRequest();
		// Make sure our field has a section
		// @TODO - handle this much more gracefully
		$tabId = craft()->request->getPost('tabId');

		// Get the Form these fields are related to
		$formId = craft()->request->getRequiredPost('formId');
		$form   = sproutForms()->forms->getFormById($formId);

		$field = new FieldModel();

		$reservedWords = array('formName', 'form', 'formId', 'statusId', 'ipAddress', 'userAgent');

		$field->id           = craft()->request->getPost('fieldId');
		$field->name         = craft()->request->getRequiredPost('name');
		$field->handle       = craft()->request->getRequiredPost('handle');
		$field->instructions = craft()->request->getPost('instructions');
		$field->required     = craft()->request->getPost('required');
		$field->translatable = (bool) craft()->request->getPost('translatable');

		$field->type = craft()->request->getRequiredPost('type');

		$typeSettings = craft()->request->getPost('types');

		if (isset($typeSettings[$field->type]))
		{
			$field->settings = $typeSettings[$field->type];
		}

		if (in_array($field->handle, $reservedWords))
		{
			$field->addError('handle', '“'.$field->handle.'” is a reserved word.');
			$variables['tabId'] = $tabId;
			$variables['field'] = $field;

			$this->_returnJson(false, $field, $form);
		}

		// Set our field context
		craft()->content->fieldContext = $form->getFieldContext();
		craft()->content->contentTable = $form->getContentTable();

		// Does our field validate?
		if (!craft()->fields->validateField($field))
		{
			SproutFormsPlugin::log("Field does not validate.");
			$variables['tabId'] = $tabId;
			$variables['field'] = $field;

			$this->_returnJson(false, $field, $form);
		}

		// Save a new field
		if (!$field->id)
		{
			$isNewField = true;
		}
		else
		{
			$isNewField = false;
			$oldHandle  = craft()->fields->getFieldById($field->id)->handle;
		}

		// Save our field
		craft()->fields->saveField($field);

		// Check if the handle is updated to also update the titleFormat
		if (!$isNewField)
		{
			// Let's update the title format
			if ($oldHandle != $field->handle && strpos($form->titleFormat, $oldHandle) !== false)
			{
				$newTitleFormat    = sproutForms()->forms->updateTitleFormat($oldHandle, $field->handle, $form->titleFormat);
				$form->titleFormat = $newTitleFormat;
			}
		}

		// Now let's add this field to our field layout
		// ------------------------------------------------------------

		// Set the field layout
		$oldFieldLayout = $form->getFieldLayout();
		$oldTabs        = $oldFieldLayout->getTabs();
		$tabName        = null;
		$response       = false;

		// If no tabs exist, let's create a
		// default one for all of our fields
		if (!$oldTabs)
		{
			$form    = sproutForms()->fields->createDefaultTab($form, $field);
			$tabName = sproutForms()->fields->getDefaultTabName();
		}
		else
		{
			$tabName  = FieldLayoutTabRecord::model()->findByPk($tabId)->name;

			if ($isNewField)
			{
				$response = sproutForms()->fields->addFieldToLayout($field, $form, $tabId);
			}
			else
			{
				$response = sproutForms()->fields->updateFieldToLayout($field, $form, $tabId);
			}
		}

		// Hand the field off to be saved in the
		// field layout of our Form Element
		if ($response)
		{
			SproutFormsPlugin::log('Field Saved');

			$this->_returnJson(true, $field, $form, $tabName);
		}
		else
		{
			$variables['tabId'] = $tabId;
			$variables['field'] = $field;
			SproutFormsPlugin::log("Couldn't save field.");
			craft()->userSession->setError(Craft::t('Couldn’t save field.'));

			$this->_returnJson(false, $field, $form);
		}
	}

	/**
	 * Edits an existing field.
	 *
	 */
	public function actionEditField()
	{
		$this->requireAjaxRequest();

		$id     = craft()->request->getRequiredParam('fieldId');
		$formId = craft()->request->getRequiredParam('formId');
		$field  = craft()->fields->getFieldById($id);
		$form   = sproutForms()->forms->getFormById($formId);

		if($field)
		{
			$fieldLayoutField = FieldLayoutFieldRecord::model()->find(array(
				'condition' => 'fieldId = :fieldId AND layoutId = :layoutId',
				'params'    => array(':fieldId' => $field->id, ':layoutId' => $form->fieldLayoutId)
			));

			$group = FieldLayoutTabRecord::model()->findByPk($fieldLayoutField->tabId);

			$this->returnJson(array(
				'success'  => true,
				'errors'   => $field->getAllErrors(),
				'field'    => array(
					'id'           => $field->id,
					'name'         => $field->name,
					'handle'       => $field->handle,
					'instructions' => $field->instructions,
					'translatable' => $field->translatable,
					'group'        => array(
						'name' => $group->name,
					),
				),
				'template' => sproutForms()->fields->getModalFieldTemplate($form, $field, $group->id),
			));
		}
		else
		{
			$message = Craft::t("The field requested to edit no longer exists.");
			SproutFormsPlugin::log($message);

			$this->returnJson(array(
				'success' => false,
				'error'   => $message,
			));
		}
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
		sproutForms()->fields->reorderFields($fieldIds);

		$this->returnJson(array(
			'success' => true
		));
	}

	private function _returnJson($success, $field, $form, $tabName = null)
	{
		$this->returnJson(array(
			'success'  => $success,
			'errors'   => $field->getAllErrors(),
			'field'    => array(
				'id'           => $field->id,
				'name'         => $field->name,
				'handle'       => $field->handle,
				'instructions' => $field->instructions,
				'translatable' => $field->translatable,
				'group'        => array(
					'name' => $tabName,
				),
			),
			'template' => $success ? false : sproutForms()->fields->getModalFieldTemplate($form, $field),
		));
	}
}
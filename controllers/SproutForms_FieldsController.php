<?php
namespace Craft;

class SproutForms_FieldsController extends BaseController
{
	/**
	 * Gets the HTML, CSS and Javascript of a field setting page.
	 *
	 * @throws HttpException
	 */
	public function actionGetFieldSettings()
	{
		$this->requireAdmin();
		$this->requireAjaxRequest();
		$formId = craft()->request->getRequiredParam('formId');
		$form   = sproutForms()->forms->getFormById($formId);

		$this->returnJson($this->_getTemplate(null, $form));
	}

	/**
	 * Save a field.
	 */
	public function actionSaveField()
	{
		$this->requireAdmin();
		$this->requirePostRequest();
		$isAjax = craft()->request->isAjaxRequest();
		// Make sure our field has a section
		// @TODO - handle this much more gracefully
		$tabId = craft()->request->getPost('tabId');

		// Get the Form these fields are related to
		$formId = craft()->request->getRequiredPost('formId');
		$form   = sproutForms()->forms->getFormById($formId);

		$field = new FieldModel();

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

		// Set our field context
		craft()->content->fieldContext = $form->getFieldContext();
		craft()->content->contentTable = $form->getContentTable();

		// Does our field validate?
		if (!craft()->fields->validateField($field))
		{
			SproutFormsPlugin::log("Field does not validate.");
			$variables['tabId'] = $tabId;
			$variables['field'] = $field;

			if ($isAjax)
			{
				$this->_returnJson(false, $field, $form);
			}
			else
			{
				// Send the field back to the template
				craft()->urlManager->setRouteVariables($variables);

				// Route our request back to the field template
				$route = craft()->urlManager->parseUrl(craft()->request);
				craft()->runController($route);
				craft()->end();
			}
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

		$postedFieldLayout = array();
		$requiredFields    = array();
		$tabName           = null;
		$response          = false;

		// If no tabs exist, let's create a
		// default one for all of our fields
		if (!$oldTabs)
		{
			$form    = sproutForms()->fields->createDefaultTab($form, $field);
			$tabName = sproutForms()->fields->getDefaultTabName();
		}
		else
		{
			if ($isAjax)
			{
				$response = sproutForms()->fields->addFieldToLayout($field, $form, $tabId);
				$tabName  = FieldLayoutTabRecord::model()->findByPk($tabId)->name;
			}
			else
			{
				foreach ($oldTabs as $oldTab)
				{
					$oldTabFields = $oldTab->getFields();

					foreach ($oldTabFields as $oldFieldLayoutField)
					{
						$postedFieldLayout[$oldTab->name][] = $oldFieldLayoutField->fieldId;

						if ($oldFieldLayoutField->required)
						{
							$requiredFields[] = $oldFieldLayoutField->fieldId;
						}
					}

					// Add our new field to the tab it belongs to
					if ($isNewField && ($tabId == $oldTab->id))
					{
						$postedFieldLayout[$oldTab->name][] = $field->id;
					}
				}
				// Set the field layout
				$fieldLayout = craft()->fields->assembleLayout($postedFieldLayout, $requiredFields);

				$fieldLayout->type = 'SproutForms_Form';
				$fieldLayout->id   = $oldFieldLayout->id;
				$form->setFieldLayout($fieldLayout);

				// save the form
				$response = sproutForms()->forms->saveForm($form);
			}
		}

		// Hand the field off to be saved in the
		// field layout of our Form Element
		if ($response)
		{
			SproutFormsPlugin::log('Field Saved');

			if ($isAjax)
			{
				$this->_returnJson(true, $field, $form, $tabName);
			}
			else
			{
				craft()->userSession->setNotice(Craft::t('Field saved.'));
				$this->redirectToPostedUrl($field);
			}
		}
		else
		{
			$variables['tabId'] = $tabId;
			$variables['field'] = $field;
			SproutFormsPlugin::log("Couldn't save field.");
			craft()->userSession->setError(Craft::t('Couldn’t save field.'));

			if ($isAjax)
			{
				$this->_returnJson(false, $field, $form);
			}
			else
			{
				// Send the field back to the template
				craft()->urlManager->setRouteVariables($variables);
			}
		}
	}

	/**
	 * Edit a field.
	 *
	 * @param array $variables
	 *
	 * @throws HttpException
	 * @throws Exception
	 */
	public function actionEditFieldTemplate(array $variables = array())
	{
		$this->requireAdmin();

		$formId = craft()->request->getSegment(3);
		$form   = sproutForms()->forms->getFormById($formId);

		if (isset($variables['fieldId']))
		{
			if (!isset($variables['field']))
			{
				$field              = craft()->fields->getFieldById($variables['fieldId']);
				$variables['field'] = $field;

				$fieldLayoutField = FieldLayoutFieldRecord::model()->find(array(
					'condition' => 'fieldId = :fieldId AND layoutId = :layoutId',
					'params'    => array(':fieldId' => $field->id, ':layoutId' => $form->fieldLayoutId)
				));

				$variables['required'] = $fieldLayoutField->required;

				$variables['tabId'] = $fieldLayoutField->tabId;

				if (!isset($variables['field']))
				{
					throw new HttpException(404);
				}
			}

			$variables['title'] = (isset($field->name) ? $field->name : "");
		}
		else
		{
			if (!isset($variables['field']))
			{
				$variables['field'] = new FieldModel();
			}

			$variables['tabId'] = null;
			$variables['title'] = Craft::t('Create a new field');
		}

		$variables['sections'] = $form->getFieldLayout()->getTabs();

		$this->renderTemplate('sproutforms/forms/_editField', $variables);
	}

	/**
	 * Delete a field.
	 *
	 * @return void
	 */
	public function actionDeleteField()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$fieldId  = craft()->request->getRequiredPost('id');
		$response = sproutForms()->forms->cleanTitleFormat($fieldId);
		$success  = craft()->fields->deleteFieldById($fieldId);
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
		sproutForms()->fields->reorderFields($fieldIds);

		$this->returnJson(array(
			'success' => true
		));
	}

	/**
	 * Loads the field settings template and returns all HTML, CSS and Javascript.
	 *
	 * @param FieldModel|null        $field
	 * @param SproutForms_FormRecord $form
	 *
	 * @return array
	 */
	private function _getTemplate(FieldModel $field = null, $form)
	{
		$data          = array();
		$data['tabId'] = null;
		$data['field'] = new FieldModel();

		if ($field)
		{
			$data['field'] = $field;
			$tabId         = craft()->request->getPost('tabId');

			if (isset($tabId))
			{
				$data['tabId'] = craft()->request->getPost('tabId');
			}

			if ($field->id != null)
			{
				$data['fieldId'] = $field->id;
			}
		}

		$data['sections'] = $form->getFieldLayout()->getTabs();
		$data['formId']   = $form->id;

		$html = craft()->templates->render('sproutforms/forms/_editModalField', $data);
		$js   = craft()->templates->getFootHtml();
		$css  = craft()->templates->getHeadHtml();

		return array(
			'html' => $html,
			'js'   => $js,
			'css'  => $css
		);
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
			'template' => $success ? false : $this->_getTemplate($field, $form),
		));
	}
}
<?php
namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;

use barrelstrength\sproutforms\SproutForms;

class FieldsController extends BaseController
{
	/**
	 * This action allows to load the modal field template.
	 *
	 */
	public function actionModalField()
	{
		$this->requireAcceptsJson();
		$formId = Craft::$app->getRequest()->getBodyParam('formId');
		$form   = SproutForms::$api->forms->getFormById($formId);

		return $this->asJson(SproutForms::$api->fields->getModalFieldTemplate($form));
	}

	/**
	 * Save a field.
	 */
	public function actionSaveField()
	{
		$this->requirePostRequest();
		$fieldsService = Craft::$app->getFields();
		// Make sure our field has a section
		// @TODO - handle this much more gracefully
		$tabId = Craft::$app->getRequest()->getBodyParam('tabId');

		// Get the Form these fields are related to
		$formId = Craft::$app->request->getRequiredBodyParam('formId');
		$form   = SproutForms::$api->forms->getFormById($formId);

		$field = $fieldsService->createField(PlainText::class);


		$field->id           = Craft::$app->getRequest()->getBodyParam('fieldId');
		$field->name         = Craft::$app->request->getRequiredBodyParam('name');
		$field->handle       = Craft::$app->request->getRequiredBodyParam('handle');
		$field->instructions = Craft::$app->getRequest()->getBodyParam('instructions');
		$field->required     = Craft::$app->getRequest()->getBodyParam('required');
		$field->translatable = (bool) Craft::$app->getRequest()->getBodyParam('translatable');

		$field->type = Craft::$app->request->getRequiredBodyParam('type');

		$typeSettings = Craft::$app->getRequest()->getBodyParam('types');

		if (isset($typeSettings[$field->type]))
		{
			$field->settings = $typeSettings[$field->type];
		}

		// Set our field context
		Craft::$app->content->fieldContext = $form->getFormModel()->getFieldContext();
		Craft::$app->content->contentTable = $form->getFormModel()->getContentTable();

		// Does our field validate?
		if (!Craft::$app->fields->validateField($field))
		{
			SproutForms::log("Field does not validate.");
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
			$oldHandle  = Craft::$app->fields->getFieldById($field->id)->handle;
		}

		// Save our field
		Craft::$app->fields->saveField($field);

		// Check if the handle is updated to also update the titleFormat
		if (!$isNewField)
		{
			// Let's update the title format
			if ($oldHandle != $field->handle && strpos($form->titleFormat, $oldHandle) !== false)
			{
				$newTitleFormat    = SproutForms::$api->forms->updateTitleFormat($oldHandle, $field->handle, $form->titleFormat);
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
			$form    = SproutForms::$api->fields->createDefaultTab($form, $field);
			$tabName = SproutForms::$api->fields->getDefaultTabName();
		}
		else
		{
			$tabName  = FieldLayoutTabRecord::model()->findByPk($tabId)->name;

			if ($isNewField)
			{
				$response = SproutForms::$api->fields->addFieldToLayout($field, $form, $tabId);
			}
			else
			{
				$response = SproutForms::$api->fields->updateFieldToLayout($field, $form, $tabId);
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
			Craft::$app->userSession->setError(Craft::t('Couldn’t save field.'));

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

		$id     = Craft::$app->getRequest()->getBodyParam('fieldId');
		$formId = Craft::$app->getRequest()->getBodyParam('formId');
		$field  = Craft::$app->fields->getFieldById($id);
		$form   = SproutForms::$api->forms->getFormById($formId);

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
				'template' => SproutForms::$api->fields->getModalFieldTemplate($form, $field, $group->id),
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
		Craft::$app->userSession->requireAdmin();
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$fieldIds = JsonHelper::decode(Craft::$app->request->getRequiredBodyParam('ids'));
		SproutForms::$api->fields->reorderFields($fieldIds);

		$this->returnJson(array(
			'success' => true
		));
	}

	private function _returnJson($success, $field, $form, $tabName = null)
	{
		return $this->asJson([
			'success'  => $success,
			'errors'   => $field->getAllErrors(),
			'field'    => [
				'id'           => $field->id,
				'name'         => $field->name,
				'handle'       => $field->handle,
				'instructions' => $field->instructions,
				'translatable' => $field->translatable,
				'group'        => [
					'name' => $tabName,
				],
			],
			'template' => $success ? false : SproutForms::$api->fields->getModalFieldTemplate($form, $field),
		]);
	}
}
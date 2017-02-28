<?php
namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\helpers\UrlHelper;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use craft\base\Field;

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
		$request       = Craft::$app->getRequest();
		$fieldsService = Craft::$app->getFields();
		// Make sure our field has a section
		// @TODO - handle this much more gracefully
		$tabId = $request->getBodyParam('tabId');

		// Get the Form these fields are related to
		$formId = $request->getRequiredBodyParam('formId');
		$form   = SproutForms::$api->forms->getFormById($formId);

		$type = $request->getRequiredBodyParam('type');

		$field = $fieldsService->createField([
			'type' => $type,
			'id' => $request->getBodyParam('fieldId'),
			'name' => $request->getBodyParam('name'),
			'handle' => $request->getBodyParam('handle'),
			'instructions' => $request->getBodyParam('instructions'),
			// @todo - add locales
			'translationMethod' =>Field::TRANSLATION_METHOD_NONE,
			'settings' => $request->getBodyParam('types.'.$type),
		]);

		// Set our field context
		Craft::$app->content->fieldContext = $form->getFormModel()->getFieldContext();
		Craft::$app->content->contentTable = $form->getFormModel()->getContentTable();

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
		if (!$fieldsService->saveField($field))
		{
			// Does not validate
			$errros = $field->getErrors();
			SproutForms::log("Field does not validate.");
			$variables['tabId'] = $tabId;
			$variables['field'] = $field;

			return $this->_returnJson(false, $field, $form);
		}

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

		if ($oldTabs)
		{
			$tabName  = FieldLayoutTabRecord::findOne($tabId)->name;

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
			SproutForms::log('Field Saved');

			return $this->_returnJson(true, $field, $form, $tabName);
		}
		else
		{
			$variables['tabId'] = $tabId;
			$variables['field'] = $field;
			SproutForms::log("Couldn't save field.");
			Craft::$app->user->setError(SproutForms::t('Couldn’t save field.'));

			return $this->_returnJson(false, $field, $form);
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
			SproutForms::log($message);

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

	public function _returnJson($success, $field, $form, $tabName = null)
	{
		return $this->asJson([
			'success'  => $success,
			'errors'   => $field->getErrors(),
			'field'    => [
				'id'           => $field->id,
				'name'         => $field->name,
				'handle'       => $field->handle,
				'instructions' => $field->instructions,
				'group'        => [
					'name' => $tabName,
				],
			],
			'template' => $success ? false : SproutForms::$api->fields->getModalFieldTemplate($form, $field),
		]);
	}
}
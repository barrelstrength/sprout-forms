<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use craft\web\Controller as BaseController;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
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
        $form = SproutForms::$app->forms->getFormById($formId);

        return $this->asJson(SproutForms::$app->fields->getModalFieldTemplate($form));
    }

    /**
     * This action allows create a default field given a type.
     *
     */
    public function actionCreateField()
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $type = $request->getBodyParam('type');
        $tabId = $request->getBodyParam('tabId');
        $tab = FieldLayoutTabRecord::findOne($tabId);
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if ($type && $form && $tab) {
            $field = SproutForms::$app->fields->createDefaultField($type, $form);

            if ($field) {
                // Set the field layout
                $oldFieldLayout = $form->getFieldLayout();
                $oldTabs = $oldFieldLayout->getTabs();
                $response = false;

                if ($oldTabs) {
                    // it's a new field
                    $response = SproutForms::$app->fields->addFieldToLayout($field, $form, $tabId);

                    return $this->_returnJson($response, $field, $form, $tab->name, $tabId);
                }
            }
        }
        // @todo - how add error messages?
        return $this->_returnJson(false, null, $form, null, $tabId);
    }

    /**
     * This action allows create a new Tab to current layout
     *
     */
    public function actionAddTab()
    {
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $name = $request->getBodyParam('name');
        $sortOrder = $request->getBodyParam('sortOrder');
        $formId = $request->getBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        if ($name && $form && $sortOrder) {
            $tab = SproutForms::$app->fields->createNewTab($name, $sortOrder, $form);

            if ($tab->id) {
                return $this->asJson([
                    'success' => true,
                    'tab' => [
                        'id' => $tab->id,
                        'name' => $tab->name
                    ]
                ]);
            }
        }
        // @todo - how add error messages?
        return $this->asJson([
            'success' => false,
            'errors' => $tab->getErrors()
        ]);
    }

	/**
	 * This action allows rename a current Tab
	 *
	 */
	public function actionRenameTab()
	{
		$this->requireAcceptsJson();

		$request = Craft::$app->getRequest();
		$name = $request->getBodyParam('name');
		$oldName = $request->getBodyParam('oldName');
		$formId = $request->getBodyParam('formId');
		$form = SproutForms::$app->forms->getFormById($formId);

		if ($name && $form) {
			$result = SproutForms::$app->fields->renameTab($name, $oldName, $form);

			if ($result) {
				return $this->asJson([
					'success' => true
				]);
			}
		}
		// @todo - how add error messages?
		return $this->asJson([
			'success' => false,
			'errors' => SproutForms::t('Unable to rename tab')
		]);
	}

    /**
     * Save a field.
     */
    public function actionSaveField()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $fieldsService = Craft::$app->getFields();
        // Make sure our field has a section
        // @TODO - handle this much more gracefully
        $tabId = $request->getBodyParam('tabId');

        // Get the Form these fields are related to
        $formId = $request->getRequiredBodyParam('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        $type    = $request->getRequiredBodyParam('type');
	      $fieldId = $request->getBodyParam('fieldId');

        $field = $fieldsService->createField([
            'type' => $type,
            'id' => $fieldId,
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'instructions' => $request->getBodyParam('instructions'),
            // @todo - add locales
            'translationMethod' => Field::TRANSLATION_METHOD_NONE,
            'settings' => $request->getBodyParam('types.'.$type),
        ]);

	      // required field validation
	      $fieldLayout = $form->getFieldLayout();
	      $fieldLayoutField = FieldLayoutFieldRecord::findOne([
	      		'layoutId' => $fieldLayout->id,
			      'tabId' => $tabId,
			      'fieldId' => $fieldId
		      ]
	      );

	      if ($fieldLayoutField)
	      {
		      $fieldLayoutField->required = $request->getBodyParam('required');
		      $fieldLayoutField->save(false);
		      $field->required = $fieldLayoutField->required;
	      }

        // Set our field context
        Craft::$app->content->fieldContext = $form->getFieldContext();
        Craft::$app->content->contentTable = $form->getContentTable();

        // Save a new field
        if (!$field->id) {
            $isNewField = true;
        } else {
            $isNewField = false;
            $oldHandle = Craft::$app->fields->getFieldById($field->id)->handle;
        }

        // Save our field
        if (!$fieldsService->saveField($field)) {
            // Does not validate
            SproutForms::error("Field does not validate.");
            $variables['tabId'] = $tabId;
            $variables['field'] = $field;

            return $this->_returnJson(false, $field, $form, null, $tabId);
        }

        // Check if the handle is updated to also update the titleFormat
        if (!$isNewField) {
            // Let's update the title format
            if ($oldHandle != $field->handle && strpos($form->titleFormat, $oldHandle) !== false) {
                $newTitleFormat = SproutForms::$app->forms->updateTitleFormat($oldHandle, $field->handle, $form->titleFormat);
                $form->titleFormat = $newTitleFormat;
            }
        }

        // Now let's add this field to our field layout
        // ------------------------------------------------------------

        // Set the field layout
        $oldFieldLayout = $form->getFieldLayout();
        $oldTabs = $oldFieldLayout->getTabs();
        $tabName = null;
        $response = false;

        if ($oldTabs) {
            $tabName = FieldLayoutTabRecord::findOne($tabId)->name;

            if ($isNewField) {
                $response = SproutForms::$app->fields->addFieldToLayout($field, $form, $tabId);
            } else {
                $response = SproutForms::$app->fields->updateFieldToLayout($field, $form, $tabId);
            }
        }

        // Hand the field off to be saved in the
        // field layout of our Form Element
        if ($response) {
            SproutForms::info('Field Saved');

            return $this->_returnJson(true, $field, $form, $tabName, $tabId);
        } else {
            $variables['tabId'] = $tabId;
            $variables['field'] = $field;
            SproutForms::error("Couldn't save field.");
            Craft::$app->getSession()->setError(SproutForms::t('Couldn’t save field.'));

            return $this->_returnJson(false, $field, $form);
        }
    }

    /**
     * Edits an existing field.
     *
     */
    public function actionEditField()
    {
        $this->requireAcceptsJson();
        $request = Craft::$app->getRequest();

        $id = $request->getBodyParam('fieldId');
        $formId = $request->getBodyParam('formId');
        $field = Craft::$app->fields->getFieldById($id);
        $form = SproutForms::$app->forms->getFormById($formId);

        if ($field) {
            $fieldLayoutField = FieldLayoutFieldRecord::findOne([
                'fieldId' => $field->id,
                'layoutId' => $form->fieldLayoutId
            ]);

            $field->required = $fieldLayoutField->required;

            $group = FieldLayoutTabRecord::findOne($fieldLayoutField->tabId);

            return $this->asJson([
                'success' => true,
                'errors' => $field->getErrors(),
                'field' => [
                    'id' => $field->id,
                    'name' => $field->name,
                    'handle' => $field->handle,
                    'instructions' => $field->instructions,
                    'required' => $field->required,
                    //'translatable' => $field->translatable,
                    'group' => [
                        'name' => $group->name,
                    ],
                ],
                'template' => SproutForms::$app->fields->getModalFieldTemplate($form, $field, $group->id),
            ]);
        } else {
            $message = SproutForms::t("The field requested to edit no longer exists.");
            SproutForms::error($message);

            return $this->asJson([
                'success' => false,
                'error' => $message,
            ]);
        }
    }

    /**
     * Reorder a field
     *
     * @return json
     */
    public function actionReorderFields()
    {
        Craft::$app->getSession()->requireAdmin();
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $fieldIds = JsonHelper::decode(Craft::$app->request->getRequiredBodyParam('ids'));
        SproutForms::$app->fields->reorderFields($fieldIds);

        return $this->asJson([
            'success' => true
        ]);
    }

    private function _returnJson($success, $field, $form, $tabName = null, $tabId = null)
    {
        return $this->asJson([
            'success' => $success,
            'errors' => $field ? $field->getErrors() : null,
            'field' => [
                'id' => $field->id,
                'name' => $field->name,
                'handle' => $field->handle,
                'icon' => $field->getIcon(),
                'htmlExample' => $field->getExampleInputHtml(),
                'required' => $field->required,
                'instructions' => $field->instructions,
                'group' => [
                    'name' => $tabName,
                    'id' => $tabId
                ],
            ],
            'template' => $success ? false : SproutForms::$app->fields->getModalFieldTemplate($form, $field),
        ]);
    }
}
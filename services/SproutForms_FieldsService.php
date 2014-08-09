<?php
namespace Craft;

class SproutForms_FieldsService extends FieldsService
{	
	/**
	 * Save a Form Field
	 * 
	 * @param  SproutForms_FormModel $form     [description]
	 * @param  FieldModel            $field    [description]
	 * @param  boolean               $validate [description]
	 * @return [type]                          [description]
	 */
	public function saveField(SproutForms_FormModel $form, FieldModel $field, $validate = true)
	{
		if (!$validate || craft()->fields->validateField($field))
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				// Set the field context
				craft()->content->fieldContext = $form->getFieldContext();
				craft()->content->contentTable = $form->getContentTable();

				$oldFieldLayoutId = $form->fieldLayoutId;

				if 	($field->id)
				{
					$fieldLayoutFields = array();

					// Save a new field layout with all form fields
					// to make sure we capture the required setting
					foreach ($form->getFields() as $oldField)
					{
						if ($oldField->id == $field->id)
						{
							$fieldLayoutFields[] = array(
								'fieldId'   => $field->id,
								'required'  => $field->required
							);
						}
						else
						{
							$fieldLayoutFields[] = array(
								'fieldId'   => $oldField->id,
								'required'  => $oldField->required
							);
						}
					}
					
					$fieldLayout = new FieldLayoutModel();
					$fieldLayout->type = 'SproutForms_Form';
					$fieldLayout->setFields($fieldLayoutFields);
					craft()->fields->saveLayout($fieldLayout, false);

					// Update the form model & record with our new field layout ID
					$form->setFieldLayout($fieldLayout);
					$form->fieldLayoutId = $fieldLayout->id;

					$formRecord = SproutForms_FormRecord::model()->findById($form->id);
					$formRecord->fieldLayoutId = $fieldLayout->id;

					// Update the form with the field layout ID
					$formRecord->save(false);

					// Clean up the old layout and fields
					craft()->fields->deleteLayoutById($oldFieldLayoutId);
					
					// Save the new field
					craft()->fields->saveField($field);

				}
				else
				{
					// Save the new field
					craft()->fields->saveField($field);
					
					// Save a new field layout with all form fields
					$fieldLayoutFields = array();
					$fieldLayoutFields[] = array(
						'fieldId'   => $field->id,
						'required'  => $field->required,
						// 'sortOrder' => $sortOrder
					);;

					foreach ($form->getFields() as $oldField)
					{
						$fieldLayoutFields[] = array(
							'fieldId'   => $oldField->id,
							'required'  => $oldField->required,
							// 'sortOrder' => $sortOrder
						);
					}
					
					$fieldLayout = new FieldLayoutModel();
					$fieldLayout->type = 'SproutForms_Form';
					$fieldLayout->setFields($fieldLayoutFields);
					craft()->fields->saveLayout($fieldLayout, false);

					// Update the form model & record with our new field layout ID
					$form->setFieldLayout($fieldLayout);
					$form->fieldLayoutId = $fieldLayout->id;

					$formRecord = SproutForms_FormRecord::model()->findById($form->id);
					$formRecord->fieldLayoutId = $fieldLayout->id;

					// Update the form with the field layout ID
					$formRecord->save(false);

					// Clean up the old layout and fields
					craft()->fields->deleteLayoutById($oldFieldLayoutId);
				}

				if ($transaction !== null)
				{
					$transaction->commit();
				}
			}
			catch (\Exception $e)
			{
					if ($transaction !== null)
					{
							$transaction->rollback();
					}

					throw $e;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Reorders fields.
	 *
	 * @param array $fieldIds
	 * @return bool
	 */
	public function reorderFields($fieldIds)
	{
		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		
		try {
			foreach ($fieldIds as $fieldOrder => $fieldId) 
			{
				$fieldLayoutFieldRecord            = $this->_getFieldLayoutFieldRecordByFieldId($fieldId);
				$fieldLayoutFieldRecord->sortOrder = $fieldOrder + 1;
				$fieldLayoutFieldRecord->save();
			}
			
			if ($transaction !== null) 
			{
				$transaction->commit();
			}
		}
		catch (\Exception $e) {
			
			if ($transaction !== null) 
			{
				$transaction->rollback();
			}
			
			throw $e;
		}
		
		return true;
	}
	
	/**
	 * Gets a Field Layout Field's record.
	 *
	 * @access private
	 * @param int $fieldId
	 * @return FieldLayoutFieldRecord
	 */
	private function _getFieldLayoutFieldRecordByFieldId($fieldId = null)
	{

		if ($fieldId) 
		{
			$record = FieldLayoutFieldRecord::model()->find('fieldId=:fieldId', array(':fieldId'=>$fieldId));
			
			if (!$record) 
			{
				throw new Exception(Craft::t('No field exists with the ID “{id}”', array(
					'id' => $fieldId
				)));
			}
		} 
		else 
		{
			$record = new FieldLayoutFieldRecord();
		}
		
		return $record;
	}	


	public function findAllFrontEndFieldTypes($fieldtypesFolder)
	{
		$frontEndFieldTypes = array();
		$frontEndFieldTypeClasses = array();

		// Find all of the built-in components
		$filter = '_SproutFormsFieldType\.php$';
		$files = IOHelper::getFolderContents($fieldtypesFolder, false, $filter);

		if ($files)
		{
			foreach ($files as $file)
			{
				$filename = IOHelper::getFileName($file, false);
				$fieldname = str_replace('_SproutFormsFieldType', '', $filename);

				$frontEndFieldTypes[$fieldname]['name'] = $fieldname;
				$frontEndFieldTypes[$fieldname]['class'] = $filename;
				$frontEndFieldTypes[$fieldname]['file'] = $file;
			}
		}

		return $frontEndFieldTypes;
	}

	public function prepareFieldTypesDropdown($fieldTypes)
	{
		$basicFields = array();
		$advancedFields = array();

		$supportedFields = array(
			'Checkboxes',
			'Dropdown',
			'MultiSelect',
			'Number',
			'PlainText',
			'RadioButtons'
		);

		// @TODO - support certain custom fields out of the box
		$supportedCustomFields = array(
			'SproutEmailField_Email',
			'SproutLinkField_Link'
		);

		foreach ($fieldTypes as $key => $fieldType) 
		{
			if (in_array($key, $supportedFields) OR in_array($key, $supportedCustomFields)) 
			{
				$basicFields[$key] = $fieldType;
			}
			else
			{
				$advancedFields[$key] = $fieldType;
			}
		}

		$fieldTypeGroups['basicFieldGroup'] = array('optgroup' => 'Basic Fields');

		foreach ($basicFields as $key => $fieldType) 
		{
			$fieldTypeGroups[$key] = $fieldType;
		}

		$fieldTypeGroups['advancedFieldGroup'] = array('optgroup' => 'Advanced Fields');

		foreach ($advancedFields as $key => $fieldType) 
		{
			$fieldTypeGroups[$key] = $fieldType;
		}

		return $fieldTypeGroups;
	}
}
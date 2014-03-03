<?php
namespace Craft;

class SproutForms_FieldService extends FieldsService
{
	private $_fieldsById;
	private $_fieldsByHandle;
	
	/**
	 * Return field given field id
	 *
	 * @param int $fieldId
	 * @return NULL|object
	 */
	public function getFieldById($fieldId)
	{
		if (!isset($this->_fieldsById) || !array_key_exists($fieldId, $this->_fieldsById))
		{
			$fieldRecord = SproutForms_FieldRecord::model()->findById($fieldId);
	
			if ($fieldRecord)
			{
				$field = SproutForms_FieldModel::populateModel($fieldRecord);
				$this->_fieldsById[$field->id] = $field;
			}
			else
			{
				return null;
			}
		}
	
		return $this->_fieldsById[$fieldId];
	}
	
	/**
	 * Return field given field handle
	 *
	 * @param int $fieldHandle
	 * @return NULL|object
	 */
	public function getFieldByHandle($fieldHandle)
	{
		if (!isset($this->_fieldsByHandle) || !array_key_exists($fieldHandle, $this->_fieldsByHandle))
		{
			$fieldRecord = SproutForms_FieldRecord::model()
			->find('handle=:handle', array(':handle' => $fieldHandle));

			if ($fieldRecord)
			{
				$field = SproutForms_FieldModel::populateModel($fieldRecord);
				$this->_fieldsByHandle[$field->handle] = $field;
			}
			else
			{
				return null;
			}
		}
	
		return $this->_fieldsByHandle[$fieldHandle];
	}
	
	/**
	 * Return a field based on a form and field handle
	 * 
	 * @param string $formHandle
	 * @param string $fieldHandle
	 * @return object|bool
	 */
	public function getFieldByFormFieldHandle($formHandle, $fieldHandle)
	{	    
		$field = craft()->db->createCommand()
		->select('csf.*')
		->from('sproutforms_fields csf')
		->join('sproutforms_forms csfo', 'csf.formId=csfo.id')
		->where('csfo.handle=:formHandle', array(':formHandle'=>$formHandle))
		->where(array('like', 'csf.handle', '%_' . $fieldHandle))
		->limit(1)
		->queryRow();
		
		if( ! $field)
		{
			return false;
		}
		
		return $this->getFieldByHandle($field['handle']);
	}
	
	/**
	 * Saves a field.
	 *
	 * @param FieldModel $field
	 * @throws \Exception
	 * @return bool
	 */
	public function saveField(FieldModel $field)
	{
		$fieldRecord = $this->_getFieldRecordById($field->id);
		$isNewField = $fieldRecord->isNewRecord();

		if (!$isNewField)
		{	
			$fieldRecord->oldHandle = $fieldRecord->handle;
		}

		$fieldRecord->formId       = $field->formId;
		$fieldRecord->name         = $field->name;
		$fieldRecord->handle       = $field->handle;
		$fieldRecord->instructions = $field->instructions;
		$fieldRecord->translatable = $field->translatable;
		$fieldRecord->type         = $field->type;
		$fieldRecord->validation   = $field->validation;

		$fieldType = $this->populateFieldType($field);
		$preppedSettings = $fieldType->prepSettings($field->settings);
		$fieldRecord->settings = $field->settings = $preppedSettings;
		$fieldType->setSettings($preppedSettings);
		$fieldType->model = $field;

		$recordValidates = $fieldRecord->validate();
		$settingsValidate = $fieldType->getSettings()->validate();

		if ($recordValidates && $settingsValidate)
		{
			$fieldRecord->handle = "formId" . $field->formId . "_" . $field->handle; // Append our FormId on the from of our field name
			$transaction = craft()->db->beginTransaction();
			try
			{
				$fieldType->onBeforeSave();
				$fieldRecord->save(false);

				// Now that we have a field ID, save it on the model
				if (!$field->id)
				{
					$field->id = $fieldRecord->id;
				}

				// Create/alter the sproutforms content table column
				$column = $fieldType->defineContentAttribute();

				if ($column)
				{
					$column = ModelHelper::normalizeAttributeConfig($column);

					if ($isNewField)
					{
						craft()->db->createCommand()->addColumn('sproutforms_content', $fieldRecord->handle, $column);
					}
					else
					{
						craft()->db->createCommand()->alterColumn('sproutforms_content', $fieldRecord->oldHandle, $column, $fieldRecord->handle);
					}
				}

				$fieldType->onAfterSave();

				$transaction->commit();
			}
			catch (\Exception $e)
			{
				$transaction->rollBack();
				throw $e;
			}

			return true;
		}
		else
		{
			$field->addErrors($fieldRecord->getErrors());
			$field->addSettingErrors($fieldType->getSettings()->getErrors());
			return false;
		}
	}
	
	/**
	 * Delete field
	 * 
	 * @param int $fieldId
	 * @return boolean
	 */
	public function deleteField($fieldId)
	{
		$fieldRecord = SproutForms_FieldRecord::model()->findById($fieldId);
		$field = SproutForms_FieldModel::populateModel($fieldRecord);
		
		// De we need to delete the content column?
		$fieldType = $this->populateFieldType($field);

		$column = $fieldType->defineContentAttribute();

		if ($column)
		{
			craft()->db->createCommand()->dropColumn('sproutforms_content', $field->handle);
		}

		// Delete the row in fields
		$affectedRows = craft()->db->createCommand()->delete('sproutforms_fields', array('id' => $field->id));

		return (bool) $affectedRows;
	}
	
	/**
	 * Return validation options
	 * 
	 * @return array
	 */
	public function getValidationOptions()
	{
		return array(
			'required' => 'required',
			'numerical' => 'numerical',
			'url' => 'url',
			'email' => 'email'
		);
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

		try
		{
			foreach ($fieldIds as $fieldOrder => $fieldId)
			{
				$fieldRecord = $this->_getFieldRecordById($fieldId);
				$fieldRecord->sortOrder = $fieldOrder+1;
				$fieldRecord->save();
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
	
	/**
	 * Gets a field's record.
	 *
	 * @access private
	 * @param int $fieldId
	 * @return FieldRecord
	 */
	private function _getFieldRecordById($fieldId = null)
	{
		if ($fieldId)
		{
			$record = SproutForms_FieldRecord::model()->findById($fieldId);

			if (!$record)
			{
				$this->_noFieldExists($fieldId);
			}
		}
		else
		{
			$record = new SproutForms_FieldRecord();
		}

		return $record;
	}
	
	/**
	 * Throws a "No field exists" exception.
	 *
	 * @access private
	 * @param int $fieldId
	 * @throws Exception
	 */
	private function _noFieldExists($fieldId)
	{
		throw new Exception(Craft::t('No field exists with the ID “{id}”', array('id' => $fieldId)));
	}
}
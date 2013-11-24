<?php
namespace Craft;

class SenorForm_FieldService extends FieldsService
{
	private $_fieldsById;
	private $_fieldsByHandle;
	
	/**
	 * Gets a field record by its ID or creates a new one.
	 *
	 * @access private
	 * @param int $fieldId
	 * @return FieldRecord
	 */
	private function _getFieldRecordById($fieldId = null)
	{
		if ($fieldId)
		{
			$fieldRecord = SenorForm_FieldRecord::model()->findById($fieldId);
	
			if (!$fieldRecord)
			{
				throw new Exception(Craft::t('No field exists with the ID “{id}”', array('id' => $fieldId)));
			}
		}
		else
		{
			$fieldRecord = new SenorForm_FieldRecord();
		}
	
		return $fieldRecord;
	}
	
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
			$fieldRecord = SenorForm_FieldRecord::model()->findById($fieldId);
	
			if ($fieldRecord)
			{
				$field = SenorForm_FieldModel::populateModel($fieldRecord);
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
			$fieldRecord = SenorForm_FieldRecord::model()
			->find('handle=:handle', array(':handle' => $fieldHandle));

			if ($fieldRecord)
			{
				$field = SenorForm_FieldModel::populateModel($fieldRecord);
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

				// Create/alter the senorform content table column
				$column = $fieldType->defineContentAttribute();

				if ($column)
				{
					$column = ModelHelper::normalizeAttributeConfig($column);

					if ($isNewField)
					{
						craft()->db->createCommand()->addColumn('senorform_content', $field->handle, $column);
					}
					else
					{
						craft()->db->createCommand()->alterColumn('senorform_content', $fieldRecord->oldHandle, $column, $field->handle);
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
		$fieldRecord = SenorForm_FieldRecord::model()->findById($fieldId);
		$field = SenorForm_FieldModel::populateModel($fieldRecord);
		
		// De we need to delete the content column?
		$fieldType = $this->populateFieldType($field);

		$column = $fieldType->defineContentAttribute();

		if ($column)
		{
			craft()->db->createCommand()->dropColumn('senorform_content', $field->handle);
		}

		// Delete the row in fields
		$affectedRows = craft()->db->createCommand()->delete('senorform_fields', array('id' => $field->id));

		return (bool) $affectedRows;
	}
	
	
	public function getValidationOptions()
	{
		return array(
				'required' => 'required',
				'numerical' => 'numerical',
				'url' => 'url',
				'email' => 'email'
		);
	}
}
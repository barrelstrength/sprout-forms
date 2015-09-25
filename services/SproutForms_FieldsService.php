<?php
namespace Craft;

class SproutForms_FieldsService extends FieldsService
{
	/**
	 * @var SproutFormsBaseField[]
	 */
	protected $registeredFields;

	/**
	 * @param  SproutForms_FormModel $form
	 * @param  FieldModel            $field
	 * @param  boolean               $validate
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function saveField(SproutForms_FormModel $form, FieldModel $field, $validate = true)
	{
		if (!$validate || craft()->fields->validateField($field))
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				if ($field->id)
				{
					$fieldLayoutFields = array();
					$sortOrder         = 0;

					// Save a new field layout with all form fields
					// to make sure we capture the required setting
					$sortOrder++;
					foreach ($form->getFields() as $oldField)
					{
						if ($oldField->id == $field->id)
						{
							$fieldLayoutFields[] = array(
								'fieldId'   => $field->id,
								'required'  => $field->required,
								'sortOrder' => $sortOrder
							);
						}
						else
						{
							$fieldLayoutFields[] = array(
								'fieldId'   => $oldField->id,
								'required'  => $oldField->required,
								'sortOrder' => $sortOrder
							);
						}
					}

					$fieldLayout       = new FieldLayoutModel();
					$fieldLayout->type = 'SproutForms_Form';
					$fieldLayout->setFields($fieldLayoutFields);

					// Update the form model & record with our new field layout ID
					$form->setFieldLayout($fieldLayout);
				}
				else
				{
					// Save the new field
					craft()->fields->saveField($field);

					// Save a new field layout with all form fields
					$fieldLayoutFields = array();
					$sortOrder         = 0;

					foreach ($form->getFields() as $oldField)
					{
						$sortOrder++;
						$fieldLayoutFields[] = array(
							'fieldId'   => $oldField->id,
							'required'  => $oldField->required,
							'sortOrder' => $sortOrder
						);
					}

					$sortOrder++;
					$fieldLayoutFields[] = array(
						'fieldId'   => $field->id,
						'required'  => $field->required,
						'sortOrder' => $sortOrder
					);

					$fieldLayout       = new FieldLayoutModel();
					$fieldLayout->type = 'SproutForms_Form';
					$fieldLayout->setFields($fieldLayoutFields);
					$form->setFieldLayout($fieldLayout);
				}

				sproutForms()->forms->saveForm($form);

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
	 * @param array $fieldIds
	 *
	 * @throws \CDbException
	 * @throws \Exception
	 * @return bool
	 */
	public function reorderFields($fieldIds)
	{
		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		try
		{
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
	 * @param int $fieldId
	 *
	 * @throws Exception
	 * @return FieldLayoutFieldRecord
	 */
	protected function _getFieldLayoutFieldRecordByFieldId($fieldId = null)
	{
		if ($fieldId)
		{
			$record = FieldLayoutFieldRecord::model()->find('fieldId=:fieldId', array(':fieldId' => $fieldId));

			if (!$record)
			{
				throw new Exception(Craft::t('No field exists with the ID “{id}”', array('id' => $fieldId)));
			}
		}
		else
		{
			$record = new FieldLayoutFieldRecord();
		}

		return $record;
	}

	public function getSproutFormsTemplates($form = null)
	{
		$templates              = array();
		$settings               = craft()->plugins->getPlugin('sproutforms')->getSettings();
		$fileConfigs            = craft()->config->get('sproutForms');
		$templateFolderOverride = $settings->templateFolderOverride;

		if (isset($form->enableTemplateOverrides))
		{
			$templateFolderOverride = $form->templateOverridesFolder;
		}

		$defaultTemplate = craft()->path->getPluginsPath().'sproutforms/templates/_special/templates/';

		// Set our defaults
		$templates['form']  = $defaultTemplate;
		$templates['tab']   = $defaultTemplate;
		$templates['field'] = $defaultTemplate;

		// See if we should override our defaults
		if ($templateFolderOverride && $fileConfigs['enableTemplateOverrides'])
		{
			$formTemplate  = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/form';
			$tabTemplate   = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/tab';
			$fieldTemplate = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/field';

			foreach (craft()->config->get('defaultTemplateExtensions') as $extension)
			{
				if (IOHelper::fileExists($formTemplate.'.'.$extension))
				{
					$templates['form'] = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/';
				}

				if (IOHelper::fileExists($tabTemplate.'.'.$extension))
				{
					$templates['tab'] = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/';
				}

				if (IOHelper::fileExists($fieldTemplate.'.'.$extension))
				{
					$templates['field'] = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/';
				}
			}
		}

		return $templates;
	}

	/**
	 * @return array|SproutFormsBaseField[]
	 */
	public function getRegisteredFields()
	{
		if (is_null($this->registeredFields))
		{
			$this->registeredFields = array();
			$results                = craft()->plugins->call('registerSproutFormsFields');

			if (!empty($results))
			{
				foreach ($results as $plugin => $fields)
				{
					if (is_array($fields) && count($fields))
					{
						/**
						 * @var SproutFormsBaseField $instance
						 */
						foreach ($fields as $instance)
						{
							$this->registeredFields[get_class($instance)] = $instance;
						}
					}
				}
			}
		}

		return $this->registeredFields;
	}

	public function prepareFieldTypesDropdown($fieldTypes)
	{
		$basicFields    = array();
		$advancedFields = array();
		$customFields   = array();

		// Supported Craft fields
		$supportedFields = array(
			'Checkboxes',
			'Dropdown',
			'MultiSelect',
			'Number',
			'PlainText',
			'RadioButtons',
		);

		// Unsupported Craft fields
		$unSupportedFields = array(
			'Assets',
			'Categories',
			'Color',
			'Date',
			'Entries',
			'Lightswitch',
			'Matrix',
			'PositionSelect',
			'RichText',
			'Table',
			'Tags',
			'Users'
		);

		foreach ($fieldTypes as $key => $fieldType)
		{
			if (in_array($key, $supportedFields))
			{
				// Sort supported fields into 'Basic' option group
				$basicFields[$key] = $fieldType;
			}
			elseif (in_array($key, $unSupportedFields))
			{
				// Sort unsupported fields into 'Advanced' option group
				$advancedFields[$key] = $fieldType;
			}
			else
			{
				// Sort all other fields into a custom group
				$customFields[$key] = $fieldType;
			}
		}

		// Grab all supported fields
		$customSproutFields = craft()->plugins->call('registerSproutField');

		foreach ($customFields as $key => $fieldType)
		{
			if (in_array($key, $customSproutFields))
			{
				// Sort supported custom fields into 'Basic' option group
				$basicFields[$key] = $fieldType;
			}
			else
			{
				// Sort unsupported custom fields into 'Advanced' option group
				$advancedFields[$key] = $fieldType;
			}
		}

		// Build our Field Type dropdown
		$fieldTypeGroups['basicFieldGroup'] = array('optgroup' => Craft::t('Basic Fields'));

		foreach ($basicFields as $key => $fieldType)
		{
			$fieldTypeGroups[$key] = $fieldType;
		}

		$fieldTypeGroups['advancedFieldGroup'] = array('optgroup' => Craft::t('Advanced Fields'));

		foreach ($advancedFields as $key => $fieldType)
		{
			$fieldTypeGroups[$key] = $fieldType;
		}

		return $fieldTypeGroups;
	}
}

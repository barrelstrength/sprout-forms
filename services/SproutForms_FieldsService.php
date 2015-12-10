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
		$templateFolderOverride = $settings->templateFolderOverride;

		if ($form->enableTemplateOverrides)
		{
			$templateFolderOverride = $form->templateOverridesFolder;
		}

		$defaultTemplate = craft()->path->getPluginsPath().'sproutforms/templates/_special/templates/';

		// Set our defaults
		$templates['form']  = $defaultTemplate;
		$templates['tab']   = $defaultTemplate;
		$templates['field'] = $defaultTemplate;
		$templates['email'] = $defaultTemplate;

		// See if we should override our defaults
		if ($templateFolderOverride)
		{
			$formTemplate  = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/form';
			$tabTemplate   = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/tab';
			$fieldTemplate = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/field';
			$emailTemplate = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/email';

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

				if (IOHelper::fileExists($emailTemplate.'.'.$extension))
				{
					$templates['email'] = craft()->path->getSiteTemplatesPath().$templateFolderOverride.'/';
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

	/**
	 * @param $type
	 *
	 * @return null|SproutFormsBaseField
	 */
	public function getRegisteredField($type)
	{
		$fields = $this->getRegisteredFields();

		foreach ($fields as $field)
		{
			if ($field->getType() == $type)
			{
				return $field;
			}
		}
	}

	/**
	 * Returns a field type selection array grouped by category
	 *
	 * Categories
	 * - Standard fields with front end rendering support
	 * - Custom fields that need to be registered using the Sprout Forms Field API
	 *
	 * @return array
	 */
	public function prepareFieldTypeSelection()
	{
		$fields         = $this->getRegisteredFields();
		$fieldTypes     = craft()->fields->getAllFieldTypes();
		$standardFields = array();
		$customFields   = array();

		if (count($fields))
		{
			// Loop through registered fields and add them to the standard group
			foreach ($fields as $field)
			{
				if (array_key_exists($field->getType(), $fieldTypes))
				{
					/**
					 * @var BaseFieldType $fieldType
					 */
					$fieldType = $fieldTypes[$field->getType()];

					$standardFields[$fieldType->getClassHandle()] = $fieldType->getName();

					// Remove the field type associate with the current field from the group
					// The remaining field types will be added to the custom group
					unset($fieldTypes[$field->getType()]);
				}
			}

			// Sort fields alphabetically by name
			asort($standardFields);

			// Add the group label to the beginning of the standard group
			$standardFields = $this->prependKeyValue($standardFields, 'standardFieldGroup', array('optgroup' => Craft::t('Standard Fields')));
		}

		if (count($fieldTypes))
		{
			// Loop through remaining field types and add them to the custom group
			foreach ($fieldTypes as $handle => $fieldType)
			{
				$customFields[$handle] = $fieldType->getName();
			}

			// Sort fields alphabetically
			ksort($customFields);

			// Add the group label to the beginning of the custom group
			$customFields = $this->prependKeyValue($customFields, 'customFieldGroup', array('optgroup' => Craft::t('Custom Fields')));
		}

		return array_merge($standardFields, $customFields);
	}

	/**
	 * Prepends a key/value pair to an array
	 *
	 * @see array_unshift()
	 *
	 * @param array  $haystack
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	protected function prependKeyValue(array $haystack, $key, $value)
	{
		$haystack       = array_reverse($haystack, true);
		$haystack[$key] = $value;

		return array_reverse($haystack, true);
	}
}

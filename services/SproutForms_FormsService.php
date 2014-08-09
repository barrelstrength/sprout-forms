<?php
namespace Craft;

class SproutForms_FormsService extends BaseApplicationComponent
{
	protected $formRecord;
	
	private $_formsByFieldId;

	/**
	 * Constructor
	 * 
	 * @param object $formRecord
	 */
	public function __construct($formRecord = null)
	{
		$this->formRecord = $formRecord;
		
		if (is_null($this->formRecord)) 
		{
			$this->formRecord = SproutForms_FormRecord::model();
		}
	}

	/**
	 * Saves a form.
	 *
	 * @param SproutForms_FormModel $form
	 * @throws \Exception
	 * @return bool
	 */
	public function saveForm(SproutForms_FormModel $form)
	{
		if ($form->id)
		{
			$formRecord = SproutForms_FormRecord::model()->findById($form->id);

			if (!$formRecord)
			{
				throw new Exception(Craft::t('No form exists with the ID “{id}”', array('id' => $form->id)));
			}

			$oldForm = SproutForms_FormModel::populateModel($formRecord);
			$isNewForm = false;
		}
		else
		{
			$formRecord = new SproutForms_FormRecord();
			$isNewForm = true;
		}

		$formRecord->name       = $form->name;
		$formRecord->handle     = $form->handle;
		$formRecord->titleFormat = $form->titleFormat;
		$formRecord->groupId    = $form->groupId;
		$formRecord->redirectUri       = $form->redirectUri;
		$formRecord->submitAction      = $form->submitAction;
		$formRecord->submitButtonText    = $form->submitButtonText;
		$formRecord->notificationRecipients       = $form->notificationRecipients;
		$formRecord->notificationSubject    = $form->notificationSubject;
		$formRecord->notificationSenderName     = $form->notificationSenderName;
		$formRecord->notificationSenderEmail     = $form->notificationSenderEmail;
		$formRecord->notificationReplyToEmail     = $form->notificationReplyToEmail;

		$formRecord->validate();
		$form->addErrors($formRecord->getErrors());

		if (!$form->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				
				// Create a Field Layout if we don't have one already
				if (!$formRecord->fieldLayoutId)
				{	
					$fieldLayout = new FieldLayoutModel();
					$fieldLayout->type = 'SproutForms_Form';
					craft()->fields->saveLayout($fieldLayout, false);

					$form->setFieldLayout($fieldLayout);
					$form->fieldLayoutId = $fieldLayout->id;

					$formRecord->fieldLayoutId = $fieldLayout->id;
				}

				// Create the content table first since the form will need it
				$oldContentTable = $this->getContentTableName($form, true);
				$newContentTable = $this->getContentTableName($form);

				// Do we need to create/rename the content table?
				if (!craft()->db->tableExists($newContentTable))
				{
					if ($oldContentTable && craft()->db->tableExists($oldContentTable))
					{
						MigrationHelper::renameTable($oldContentTable, $newContentTable);
					}
					else
					{
						$this->_createContentTable($newContentTable);
					}
				}

				if (craft()->elements->saveElement($form))
				{
					// Now that we have an element ID, save it on the other stuff
					if ($isNewForm)
					{
						$formRecord->id = $form->id;
					}

					// Save our Form Settings
					$formRecord->save(false);

					if ($transaction !== null)
					{
						$transaction->commit();
					}

					return true;
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
		}
	}

	/**
	 * Delete form
	 * 
	 * @param int $id
	 * @return boolean
	 */
	public function deleteForm(SproutForms_FormModel $form)
	{
		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			$originalContentTable = craft()->content->contentTable;
			$contentTable = $this->getContentTableName($form);
			craft()->content->contentTable = $contentTable;

			// Delete form fields
			foreach ($form->getFields() as $field)
			{
				craft()->fields->deleteField($field);
			}

			// Delete the Field Layout
			craft()->fields->deleteLayoutById($form->fieldLayoutId);

			// Drop the content table
			craft()->db->createCommand()->dropTable($contentTable);
			craft()->content->contentTable = $originalContentTable;

			// Delete the Element and Form
			craft()->elements->deleteElementById($form->id);
			

			if ($transaction !== null)
			{
				$transaction->commit();
			}

			return true;
		}
		catch (\Exception $e)
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}

			throw $e;
		}
	}

	/**
	 * Returns the content table name for a given Form field.
	 *
	 * @param FormModel $form
	 * @param bool $useOldHandle
	 * @return string|false
	 */
	public function getContentTableName(SproutForms_FormModel $form, $useOldHandle = false)
	{
		if ($useOldHandle)
		{
			if (!$form->oldHandle)
			{
				return false;
			}

			$handle = $form->oldHandle;
		}
		else
		{
			$handle = $form->handle;
		}

		$name = '_'.StringHelper::toLowerCase($handle);
		
		return 'sproutformscontent'.$name;
	}

	/**
	 * Creates the content table for a Form.
	 *
	 * @access private
	 * @param string $name
	 */
	private function _createContentTable($name)
	{
		craft()->db->createCommand()->createTable($name, array(
			'elementId' => array('column' => ColumnType::Int, 'null' => false),
			'locale'    => array('column' => ColumnType::Locale, 'null' => false),
			'title'     => array('column' => ColumnType::Varchar)
		));

		craft()->db->createCommand()->createIndex($name, 'elementId,locale', true);
		craft()->db->createCommand()->addForeignKey($name, 'elementId', 'elements', 'id', 'CASCADE', null);
		craft()->db->createCommand()->addForeignKey($name, 'locale', 'locales', 'locale', 'CASCADE', 'CASCADE');
	}

	/**
	 * Return form by form id
	 * 
	 * @param int $formId
	 * @return object form record
	 */
	public function getFormById($formId)
	{
		$formRecord = $this->formRecord->findById($formId);
		
		if ($formRecord) 
		{
			return SproutForms_FormModel::populateModel($formRecord);
		} 
		else 
		{
			return null;
		}
	}

	/**
	 * Return form by form handle
	 *
	 * @param string $handle
	 * @return object form record
	 */
	public function getFormByHandle($handle)
	{
		$formRecord = $this->formRecord->find('handle=:handle', array(
			':handle' => $handle
		));
		
		if ($formRecord) 
		{
			return SproutForms_FormModel::populateModel($formRecord);
		} 
		else 
		{
			return null;
		}
	}

	/**
	 * Get all Fallbacks from the database.
	 *
	 * @return array
	 */
	public function getAllForms()
	{
		$records = $this->formRecord->findAll(array('order'=>'name'));
		return SproutForms_FormModel::populateModels($records);
	}




	

	// ============================================================
	// @TODO - Review the below functions copied over from the last plugin
	// ============================================================
	
	
	
	/**
	 * Return form given associated field id
	 *
	 * @param int $fieldId
	 * @return NULL|object
	 */
	public function getFormByFieldId($fieldId)
	{
		if (!isset($this->_formsById) || !array_key_exists($fieldId, $this->_formsById)) {
			$formRecord = $this->formRecord->with(array(
				'field' => array(
					'select' => false,
					'joinType' => 'INNER JOIN',
					'condition' => 'field.id=' . $fieldId
				)
			))->find();
			
			if ($formRecord) {
				$form                            = SproutForms_FormModel::populateModel($formRecord);
				$this->_formsByFieldId[$fieldId] = $form;
			} else {
				return null;
			}
		}
		
		return $this->_formsByFieldId[$fieldId];
	}
	
	

}
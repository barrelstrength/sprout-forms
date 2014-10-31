<?php
namespace Craft;

class SproutForms_FormsService extends BaseApplicationComponent
{
	public $activeEntries;
	public $activeCpEntry;

	protected $formRecord;

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
	 * Returns a criteria model for SproutForms_Form elements
	 *
	 * @param array $attributes
	 *
	 * @return ElementCriteriaModel
	 * @throws Exception
	 */
	public function getCriteria(array $attributes=array())
	{
		return craft()->elements->getCriteria('SproutForms_Form', $attributes);
	}

	/**
	 * @param SproutForms_FormModel $form
	 *
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

			$hasLayout = count($form->getFieldLayout()->getFields()) > 0;
			
			// Add the oldHandle to our model so we can determine if we
			// need to rename the content table
			$form->oldHandle = $formRecord->getOldHandle();

		}
		else
		{
			$formRecord = new SproutForms_FormRecord();
			$isNewForm = true;
		}

		// Create our new Form Record
		$formRecord->name                     = $form->name;
		$formRecord->handle                   = $form->handle;
		$formRecord->titleFormat              = ($form->titleFormat ? $form->titleFormat : "{dateCreated|date('D, d M Y H:i:s')}");
		$formRecord->displaySectionTitles     = $form->displaySectionTitles;
		$formRecord->groupId                  = $form->groupId;
		$formRecord->redirectUri              = $form->redirectUri;
		$formRecord->submitAction             = $form->submitAction;
		$formRecord->submitButtonText         = $form->submitButtonText;
		$formRecord->notificationRecipients   = $form->notificationRecipients;
		$formRecord->notificationSubject      = $form->notificationSubject;
		$formRecord->notificationSenderName   = $form->notificationSenderName;
		$formRecord->notificationSenderEmail  = $form->notificationSenderEmail;
		$formRecord->notificationReplyToEmail = $form->notificationReplyToEmail;

		$formRecord->validate();
		$form->addErrors($formRecord->getErrors());
		
		if (!$form->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				// Set the field context
				craft()->content->fieldContext = $form->getFieldContext();
				craft()->content->contentTable = $form->getContentTable();
				
				if ($isNewForm) 
				{	
					$fieldLayout = new FieldLayoutModel();
					$fieldLayout->type = 'SproutForms_Form';

					// Save the field layout
					craft()->fields->saveLayout($fieldLayout);

					// Assign our new layout id info to our 
					// form model and records
					$form->fieldLayoutId = $fieldLayout->id;
					$form->setFieldLayout($fieldLayout);
					$formRecord->fieldLayoutId = $fieldLayout->id;
				}
				else
				{	
					// If we have a layout use it, otherwise
					// since this is an existing form, grab the oldForm layout
					if ($hasLayout)
					{
						// Delete our previous record
						craft()->fields->deleteLayoutById($oldForm->fieldLayoutId);

						$fieldLayout = $form->getFieldLayout();

						// Save the field layout
						craft()->fields->saveLayout($fieldLayout);

						// Assign our new layout id info to our 
						// form model and records
						$form->fieldLayoutId = $fieldLayout->id;
						$form->setFieldLayout($fieldLayout);
						$formRecord->fieldLayoutId = $fieldLayout->id;
					}
					else
					{
						// We don't have a field layout right now
						$form->fieldLayoutId = NULL;
					}
					
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
	 * Removes a form and related records from the database
	 *
	 * @param SproutForms_FormModel $form
	 *
	 * @throws \CDbException
	 * @throws \Exception
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
	 * Returns an array of models for forms found in the database
	 *
	 * @return SproutForms_FormModel|array|null
	 */
	public function getAllForms()
	{
		$attributes	= array('order' => 'name');

		return $this->getCriteria($attributes)->find();
	}

	/**
	 * Returns a form model if one is found in the database by id
	 * 
	 * @param int $formId
	 * @return null|SproutForms_FormModel
	 */
	public function getFormById($formId)
	{
		return $this->getCriteria(array('limit' => 1, 'id' => $formId))->first();
	}

	/**
	 * Returns a form model if one is found in the database by handle
	 *
	 * @param string $handle
	 * @return false|SproutForms_FormModel
	 */
	public function getFormByHandle($handle)
	{
		return $this->getCriteria(array('limit' => 1, 'handle' => $handle))->first();
	}

	/**
	 * Returns the content table name for a given form field
	 *
	 * @param SproutForms_FormModel $form
	 * @param bool $useOldHandle
	 *
	 * @return string|false
	 */
	public function getContentTableName(SproutForms_FormModel $form, $useOldHandle=false)
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
	 * @param $formId
	 *
	 * @return string
	 */
	public function getContentTable($formId)
	{
		$form = $this->getFormById($formId);

		if ($form)
		{
			return sprintf('sproutformscontent_%s', trim(strtolower($form->handle)));
		}

		return 'content';
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
}

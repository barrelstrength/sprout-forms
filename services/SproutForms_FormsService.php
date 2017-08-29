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
	public function getCriteria(array $attributes = array())
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
		$formRecord = new SproutForms_FormRecord();
		$isNewForm  = true;

		if ($form->id)
		{
			$formRecord = SproutForms_FormRecord::model()->findById($form->id);

			if (!$formRecord)
			{
				throw new Exception(Craft::t('No form exists with the ID “{id}”', array('id' => $form->id)));
			}

			$oldForm   = SproutForms_FormModel::populateModel($formRecord);
			$isNewForm = false;

			$hasLayout = count($form->getFieldLayout()->getFields()) > 0;

			// Add the oldHandle to our model so we can determine if we
			// need to rename the content table
			$form->oldHandle = $formRecord->getOldHandle();

			if ($form->saveAsNew)
			{
				$form->name   = $oldForm->name;
				$form->handle = $oldForm->handle;
				$form->oldHandle = null;
			}
		}

		// Create our new Form Record
		$formRecord->name                     = $form->name;
		$formRecord->handle                   = $form->handle;
		$formRecord->titleFormat              = ($form->titleFormat ? $form->titleFormat : "{dateCreated|date('D, d M Y H:i:s')}");
		$formRecord->displaySectionTitles     = $form->displaySectionTitles;
		$formRecord->groupId                  = $form->groupId;
		$formRecord->redirectUri              = $form->redirectUri;
		$formRecord->submitAction             = $form->submitAction;
		$formRecord->saveData                 = $form->saveData;
		$formRecord->submitButtonText         = $form->submitButtonText;
		$formRecord->notificationEnabled      = $form->notificationEnabled;
		$formRecord->notificationRecipients   = $form->notificationRecipients;
		$formRecord->notificationSubject      = $form->notificationSubject;
		$formRecord->notificationSenderName   = $form->notificationSenderName;
		$formRecord->notificationSenderEmail  = $form->notificationSenderEmail;
		$formRecord->notificationReplyToEmail = $form->notificationReplyToEmail;
		$formRecord->enableTemplateOverrides  = $form->enableTemplateOverrides;
		$formRecord->templateOverridesFolder  = $form->templateOverridesFolder;
		$formRecord->enableFileAttachments    = $form->enableFileAttachments;

		// @todo - Why do we need these now?
		// Things were working fine without these and now 2.5 is throwing errors unless we set them explicitly
		if ($isNewForm)
		{
			$formRecord->dateCreated = date('Y-m-d h:m:s');
			$formRecord->dateUpdated = date('Y-m-d h:m:s');
		}

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
					$fieldLayout = $form->getFieldLayout();

					// Save the field layout
					craft()->fields->saveLayout($fieldLayout);

					// Assign our new layout id info to our form model and records
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
						$form->fieldLayoutId = null;
					}
				}

				// Create the content table first since the form will need it
				$oldContentTable = $this->getContentTableName($form, true);
				$newContentTable = $this->getContentTableName($form);

				// Do we need to create/rename the content table?
				if (!craft()->db->tableExists($newContentTable) && !$form->saveAsNew)
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
			$originalContentTable          = craft()->content->contentTable;
			$contentTable                  = $this->getContentTableName($form);
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
		$attributes = array('order' => 'name');

		return $this->getCriteria($attributes)->find();
	}

	/**
	 * Returns a form model if one is found in the database by id
	 *
	 * @param int $formId
	 *
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
	 *
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
	 * @param bool                  $useOldHandle
	 *
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

		$name = '_' . StringHelper::toLowerCase($handle);

		return 'sproutformscontent' . $name;
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
	 *
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
	 * Returns the value of a given field
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return SproutForms_FormRecord
	 */
	public function getFieldValue($field, $value)
	{
		$criteria            = new \CDbCriteria();
		$criteria->condition = "{$field} =:value";
		$criteria->params    = array(':value' => $value);
		$criteria->limit     = 1;

		$result = SproutForms_FormRecord::model()->find($criteria);

		return $result;
	}

	/**
	 * Remove a field handle from title format
	 *
	 * @param int $fieldId
	 *
	 * @return string newTitleFormat
	 */
	public function cleanTitleFormat($fieldId)
	{
		$field = craft()->fields->getFieldById($fieldId);

		if ($field)
		{
			$context    = explode(":", $field->context);
			$formId     = $context[1];
			$formRecord = SproutForms_FormRecord::model()->findById($formId);

			// Check if the field is in the titleformat
			if (strpos($formRecord->titleFormat, $field->handle) !== false)
			{
				// Let's remove the field from the titleFormat
				$newTitleFormat          = preg_replace('/\{' . $field->handle . '.*\}/', '', $formRecord->titleFormat);
				$formRecord->titleFormat = $newTitleFormat;
				$formRecord->save(false);

				return $formRecord->titleFormat;
			}
		}

		return null;
	}

	/**
	 * Update a field handle from title format
	 *
	 * @param string $oldHandle
	 * @param string $newHandle
	 * @param string $titleFormat
	 *
	 * @return string newTitleFormat
	 */
	public function updateTitleFormat($oldHandle, $newHandle, $titleFormat)
	{
		// Let's replace the field from the titleFormat
		$newTitleFormat = str_replace($oldHandle, $newHandle, $titleFormat);

		return $newTitleFormat;
	}

	/**
	 * Create a secuencial string for the "name" and "handle" fields if they are already taken
	 *
	 * @param string
	 * @param string
	 * return string
	 */
	public function getFieldAsNew($field, $value)
	{
		$newField = null;
		$i        = 1;
		$band     = true;
		do
		{
			$newField = $field == "handle" ? $value . $i : $value . " " . $i;
			$form     = sproutForms()->forms->getFieldValue($field, $newField);
			if (is_null($form))
			{
				$band = false;
			}

			$i++;
		}
		while ($band);

		return $newField;
	}

	/**
	 * Sprout Forms Send Notification service.
	 *
	 * @param SproutForms_FormModel  $form
	 * @param SproutForms_EntryModel $entry
	 * @param array $post
	 *
	 * @return boolean
	 */
	public function sendNotification(SproutForms_FormModel $form, SproutForms_EntryModel $entry, $post = null)
	{
		// Get our recipients
		$recipients = ArrayHelper::stringToArray($form->notificationRecipients);
		$recipients = array_unique($recipients);
		$response   = false;

		if (count($recipients))
		{
			$email         = new EmailModel();
			$tabs          = $form->getFieldLayout()->getTabs();
			$templatePaths = sproutForms()->fields->getSproutFormsTemplates($form);
			$emailTemplate = $templatePaths['email'];

			// Set our Sprout Forms Email Template path
			craft()->templates->setTemplatesPath($emailTemplate);

			$email->htmlBody = craft()->templates->render(
				'email', array(
					'formName' => $form->name,
					'tabs'     => $tabs,
					'element'  => $entry
				)
			);

			craft()->templates->setTemplatesPath(craft()->path->getCpTemplatesPath());

			if (is_null($post))
			{
				$post = $_POST;
			}

			$post = (object) $post;

			$email->fromEmail = $form->notificationSenderEmail;
			$email->fromName  = $form->notificationSenderName;
			$email->subject   = $form->notificationSubject;

			try
			{
				// Has a custom subject been set for this form?
				if ($form->notificationSubject)
				{
					$email->subject = craft()->templates->renderObjectTemplate($form->notificationSubject, $post, true);
				}

				$email->subject = sproutForms()->encodeSubjectLine($email->subject);

				// custom replyTo has been set for this form
				if ($form->notificationReplyToEmail)
				{
					$email->replyTo = craft()->templates->renderObjectTemplate($form->notificationReplyToEmail, $post, true);

					if (!filter_var($email->replyTo, FILTER_VALIDATE_EMAIL))
					{
						$email->replyTo = null;
					}
				}

				foreach ($recipients as $emailAddress)
				{
					$email->toEmail = craft()->templates->renderObjectTemplate($emailAddress, $post, true);

					if (filter_var($email->toEmail, FILTER_VALIDATE_EMAIL))
					{
						$options =
							array(
								'sproutFormsEntry'      => $entry,
								'enableFileAttachments' => $form->enableFileAttachments,
							);
						craft()->email->sendEmail($email, $options);
					}
				}

				$response = true;
			}
			catch (\Exception $e)
			{
				$response = false;
				SproutFormsPlugin::log($e->getMessage(), LogLevel::Error);
			}
		}

		return $response;
	}

	/**
	 * Loads the sprout modal field via ajax.
	 *
	 * @param SproutForms_FormRecord $form
	 * @param FieldModel|null        $field
	 *
	 * @return array
	 */
	public function getModalFieldTemplate($form, FieldModel $field = null)
	{
		$data          = array();
		$data['tabId'] = null;
		$data['field'] = new FieldModel();

		if ($field)
		{
			$data['field'] = $field;
			$tabId         = craft()->request->getPost('tabId');

			if (isset($tabId))
			{
				$data['tabId'] = craft()->request->getPost('tabId');
			}

			if ($field->id != null)
			{
				$data['fieldId'] = $field->id;
			}
		}

		$data['sections'] = $form->getFieldLayout()->getTabs();
		$data['formId']   = $form->id;

		$html = craft()->templates->render('sproutforms/forms/_editFieldModal', $data);
		$js   = craft()->templates->getFootHtml();
		$css  = craft()->templates->getHeadHtml();

		return array(
			'html' => $html,
			'js'   => $js,
			'css'  => $css
		);
	}

	/**
	 * Removes forms and related records from the database given the ids
	 *
	 * @param mixed ids
	 *
	 * @throws \CDbException
	 * @throws \Exception
	 * @return boolean
	 */
	public function deleteForms($ids)
	{
		foreach ($ids as $key => $id)
		{
			$form = sproutForms()->forms->getFormById($id);

			if ($form)
			{
				sproutForms()->forms->deleteForm($form);
			}
			else
			{
				SproutFormsPlugin::log("Can't delete the form with id: ".$id);
			}
		}

		return true;
	}

	/**
	 * Creates a form with a empty default tab
	 *
	 * @param string $name
	 * @param string $handle
	 *
	 * @throws \CDbException
	 * @throws \Exception
	 * @return boolean
	 */

	public function createNewForm($name = null, $handle = null)
	{
		$form     = new SproutForms_FormModel();
		$name     = empty($name) ? 'Form' : $name ;
		$handle   = empty($handle) ? 'form' : $handle;
		$settings = craft()->plugins->getPlugin('sproutforms')->getSettings();

		if ($settings['enableSaveData'])
		{
			if ($settings['enableSaveDataPerFormBasis'])
			{
				$form->saveData = $settings['saveDataByDefault'];
			}
		}

		$form->name   = sproutForms()->forms->getFieldAsNew('name', $name);
		$form->handle = sproutForms()->forms->getFieldAsNew('handle', $handle);
		// Set default tab
		$field = null;
		$form  = sproutForms()->fields->addDefaultTab($form, $field);

		if (sproutForms()->forms->saveForm($form))
		{
			// Let's delete the default field
			if (isset($field) && $field->id)
			{
				craft()->fields->deleteFieldById($field->id);
			}

			return $form;
		}

		return false;
	}

	public function installDefaultSettings()
	{
		$defaultSettings = '{"pluginNameOverride":null,"templateFolderOverride":"","enablePerFormTemplateFolderOverride":"","enablePayloadForwarding":"","enableSaveData":"1","enableSaveDataPerFormBasis":"", "saveDataByDefault":"1"}';

		craft()->db->createCommand()->update('plugins',
				array('settings' => $defaultSettings),
				array('class' => 'SproutForms')
			);

		return true;
	}

	/**
	 *@return null|HttpException
	*/
	public function userCanEditForms()
	{
		if (!craft()->userSession->checkPermission('manageSproutFormsForms'))
		{
			throw new HttpException(401, Craft::t("Not authorized to edit Forms."));
		}
	}
}
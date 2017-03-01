<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;
use craft\helpers\StringHelper;
use craft\helpers\MigrationHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\models\Form as FormModel;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\migrations\CreateFormContentTable;

class Forms extends Component
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
			$this->formRecord = new FormRecord();
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
		return Craft::$app->elements->getCriteria(FormElement::class, $attributes);
	}

	/**
	 * @param FormModel $form
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function saveForm(FormModel $form)
	{
		$formRecord = new FormRecord();
		$isNewForm  = true;

		if ($form->id)
		{
			$formRecord = FormRecord::findOne($form->id);

			if (!$formRecord)
			{
				throw new Exception(SproutForms::t('No form exists with the ID “{id}”', ['id' => $form->id]));
			}

			$oldForm   = $formRecord;
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
		$formRecord->savePayload              = $form->savePayload;
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
			$transaction = Craft::$app->db->getTransaction() === null ? Craft::$app->db->beginTransaction() : null;
			try
			{
				// Set the field context
				Craft::$app->content->fieldContext = $form->getFieldContext();
				Craft::$app->content->contentTable = $form->getContentTable();

				if ($isNewForm)
				{
					$fieldLayout = $form->getFieldLayout();

					// Save the field layout
					Craft::$app->getFields()->saveLayout($fieldLayout);

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
						Craft::$app->getFields()->deleteLayoutById($oldForm->fieldLayoutId);

						$fieldLayout = $form->getFieldLayout();

						// Save the field layout
						Craft::$app->getFields()->saveLayout($fieldLayout);

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
				if (!Craft::$app->db->tableExists($newContentTable) && !$form->saveAsNew)
				{
					if ($oldContentTable && Craft::$app->db->tableExists($oldContentTable))
					{
						MigrationHelper::renameTable($oldContentTable, $newContentTable);
					}
					else
					{
						$this->_createContentTable($newContentTable);
					}
				}

				// Craft 3 new stuff
				$formElement = new FormElement($formRecord->attributes);

				if (Craft::$app->elements->saveElement($formElement, false))
				{
					// Save our Form Settings - Craft3 afterSave form element
					//$formRecord->save(false);
					$form->id = $formElement->id;

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
		$transaction = Craft::$app->db->getCurrentTransaction() === null ? Craft::$app->db->beginTransaction() : null;
		try
		{
			$originalContentTable          = Craft::$app->content->contentTable;
			$contentTable                  = $this->getContentTableName($form);
			Craft::$app->content->contentTable = $contentTable;

			// Delete form fields
			foreach ($form->getFields() as $field)
			{
				Craft::$app->getFields()->deleteField($field);
			}

			// Delete the Field Layout
			Craft::$app->getFields()->deleteLayoutById($form->fieldLayoutId);

			// Drop the content table
			Craft::$app->db->createCommand()->dropTable($contentTable);
			Craft::$app->content->contentTable = $originalContentTable;

			// Delete the Element and Form
			Craft::$app->elements->deleteElementById($form->id);

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
	 * @param int $siteId
	 * @return SproutForms_FormModel|array|null
	 */
	public function getAllForms(int $siteId = null)
	{
		$query = FormElement::find();
		$query->siteId($siteId);
		$query->orderBy(['name' => SORT_ASC]);
		// @todo - research next function
		#$query->enabledForSite(false);

		return $query->all();
	}

	/**
	 * Returns a form model if one is found in the database by id
	 *
	 * @param int $formId
	 * @param int $siteId
	 *
	 * @return null|FormElement
	 */
	public function getFormById(int $formId, int $siteId = null)
	{
		$query = FormElement::find();
		$query->id($formId);
		$query->siteId($siteId);
		// @todo - research next function
		#$query->enabledForSite(false);

		return $query->one();
	}

	/**
	 * Returns a form model if one is found in the database by handle
	 *
	 * @param string $handle
	 * @param int    $siteId
	 *
	 * @return false|SproutForms_FormModel
	 */
	public function getFormByHandle(string $handle, int $siteId = null)
	{
		$query = FormElement::find();
		$query->handle($handle);
		$query->siteId($siteId);
		// @todo - research next function
		#$query->enabledForSite(false);

		return $query->one();
	}

	/**
	 * Returns the content table name for a given form field
	 *
	 * @param FormElement $form
	 * @param bool       $useOldHandle
	 *
	 * @return string|false
	 */
	public function getContentTableName(FormModel $form, $useOldHandle = false)
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

		return '{{%sproutformscontent' . $name.'}}';
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
		$migration = new CreateFormContentTable([
			'tableName' => $name
		]);

		ob_start();
		$migration->up();
		ob_end_clean();
	}

	/**
	 * Returns the value of a given field
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return FormRecord
	 */
	public function getFieldValue($field, $value)
	{
		$result = FormRecord::findOne([$field => $value]);

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
		$field = Craft::$app->getFields()->getFieldById($fieldId);

		if ($field)
		{
			$context    = explode(":", $field->context);
			$formId     = $context[1];
			$formRecord = FormRecord::find($formId)->one();

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
			$form     = $this->getFieldValue($field, $newField);
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
			Craft::$app->templates->setTemplatesPath($emailTemplate);

			$email->htmlBody = Craft::$app->templates->render(
				'email', array(
					'formName' => $form->name,
					'tabs'     => $tabs,
					'element'  => $entry
				)
			);

			Craft::$app->templates->setTemplatesPath(Craft::$app->path->getCpTemplatesPath());

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
					$email->subject = Craft::$app->templates->renderObjectTemplate($form->notificationSubject, $post, true);
				}

				$email->subject = sproutForms()->encodeSubjectLine($email->subject);

				// custom replyTo has been set for this form
				if ($form->notificationReplyToEmail)
				{
					$email->replyTo = Craft::$app->templates->renderObjectTemplate($form->notificationReplyToEmail, $post, true);

					if (!filter_var($email->replyTo, FILTER_VALIDATE_EMAIL))
					{
						$email->replyTo = null;
					}
				}

				foreach ($recipients as $emailAddress)
				{
					$email->toEmail = Craft::$app->templates->renderObjectTemplate($emailAddress, $post, true);

					if (filter_var($email->toEmail, FILTER_VALIDATE_EMAIL))
					{
						$options =
							array(
								'sproutFormsEntry'      => $entry,
								'enableFileAttachments' => $form->enableFileAttachments,
							);
						Craft::$app->email->sendEmail($email, $options);
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
	 * @param FormRecord $form
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
			$tabId         = Craft::$app->request->getPost('tabId');

			if (isset($tabId))
			{
				$data['tabId'] = Craft::$app->request->getPost('tabId');
			}

			if ($field->id != null)
			{
				$data['fieldId'] = $field->id;
			}
		}

		$data['sections'] = $form->getFieldLayout()->getTabs();
		$data['formId']   = $form->id;

		$html = Craft::$app->templates->render('sproutforms/forms/_editFieldModal', $data);
		$js   = Craft::$app->templates->getFootHtml();
		$css  = Craft::$app->templates->getHeadHtml();

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
		$form   = new FormModel();
		$name   = empty($name) ? 'Form' : $name ;
		$handle = empty($handle) ? 'form' : $handle;

		$form->name   = $this->getFieldAsNew('name', $name);
		$form->handle = $this->getFieldAsNew('handle', $handle);
		// Set default tab
		$field = null;
		$form  = SproutForms::$api->fields->addDefaultTab($form, $field);

		if ($this->saveForm($form))
		{
			// Let's delete the default field
			if (isset($field) && $field->id)
			{
				Craft::$app->getFields()->deleteFieldById($field->id);
			}

			return $form;
		}

		return false;
	}

	/**
	 * Returns a form model by its ID.
	 *
	 * @param int $formId
	 *
	 * @return FormModel|null
	 */
	public function getFormModelById(int $formId)
	{
		if (($formRecord = FormRecord::findOne($formId)) === null)
		{
			return null;
		}

		return $model = new FormModel($formRecord->toArray([
			'id',
			'name',
			'handle',
			'fieldLayoutId',
		]));
	}
}

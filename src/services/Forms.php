<?php
namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\migrations\CreateFormContentTable;
use Craft;
use yii\base\Component;
use craft\helpers\StringHelper;
use craft\helpers\MigrationHelper;
use craft\helpers\ArrayHelper;
use craft\mail\Message;


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
	 * @param FormElement $form
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function saveForm(FormElement $form)
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

		$form->titleFormat = ($form->titleFormat ? $form->titleFormat : "{dateCreated|date('D, d M Y H:i:s')}");

		$form->validate();

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
					$form->fieldLayoutId = $fieldLayout->id;
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
						$form->fieldLayoutId = $fieldLayout->id;
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

				if (Craft::$app->elements->saveElement($form, false))
				{
					// Save our Form Settings - Craft3 afterSave form element
					//$form->save(false);

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
	 * @param FormElement $form
	 *
	 * @throws \CDbException
	 * @throws \Exception
	 * @return boolean
	 */
	public function deleteForm(FormElement $form)
	{
		$transaction = Craft::$app->db->getTransaction() === null ? Craft::$app->db->beginTransaction() : null;
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
			Craft::$app->db->createCommand()
			->dropTable($contentTable)
			->execute();

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
	public function getContentTableName(FormElement $form, $useOldHandle = false)
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
	 * @return $form
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
	 * @param FormElement  $form
	 * @param EntryElement $entry
	 * @param array $post
	 *
	 * @return boolean
	 */
	public function sendNotification(FormElement $form, EntryElement $entry, $post = null)
	{
		// Get our recipients
		$recipients = ArrayHelper::toArray($form->notificationRecipients);
		$recipients = array_unique($recipients);
		$response   = false;
		$view       = Craft::$app->getView();

		if (count($recipients))
		{
			$message         = new Message();
			$tabs          = $form->getFieldLayout()->getTabs();
			$templatePaths = SproutForms::$app->fields->getSproutFormsTemplates($form);
			$emailTemplate = $templatePaths['email'];

			// Set our Sprout Forms Email Template path
			$view->setTemplatesPath($emailTemplate);

			$htmlBodyTemplate = $view->renderTemplate(
				'email', [
					'formName' => $form->name,
					'tabs'     => $tabs,
					'element'  => $entry
				]
			);

			$message->setHtmlBody($htmlBodyTemplate);

			$view->setTemplatesPath(Craft::$app->path->getCpTemplatesPath());

			if (is_null($post))
			{
				$post = $_POST;
			}

			$post = (object) $post;

			$message->setFrom($form->notificationSenderEmail);
			// @todo - how set from name on craft3?
			#$message->setFrom  = $form->notificationSenderName;
			$message->setSubject($form->notificationSubject);

			$mailer = Craft::$app->getMailer();

			try
			{
				$subject = null;
				// Has a custom subject been set for this form?
				if ($form->notificationSubject)
				{
					$subject = $view->renderObjectTemplate($form->notificationSubject, $post, true);
				}

				$message->setSubject(SproutForms::$app->encodeSubjectLine($subject));

				// custom replyTo has been set for this form
				if ($form->notificationReplyToEmail)
				{
					$repleyTo = $view->renderObjectTemplate($form->notificationReplyToEmail, $post, true);

					$message->setReplyTo($repleyTo);

					if (!filter_var($repleyTo, FILTER_VALIDATE_EMAIL))
					{
						$message->setReplyTo(null);
					}
				}

				foreach ($recipients as $emailAddress)
				{
					$toEmail = $view->renderObjectTemplate($emailAddress, $post, true);
					$message->setTo($toEmail);

					if (filter_var($toEmail, FILTER_VALIDATE_EMAIL))
					{
						// @todo - add to the event
						/*$options =
							array(
								'sproutFormsEntry'      => $entry,
								'enableFileAttachments' => $form->enableFileAttachments,
							);*/

						$mailer->send($message);
					}
				}

				$response = true;
			}
			catch (\Exception $e)
			{
				$response = false;
				SproutForms::log($e->getMessage(), 'error');
			}
		}

		return $response;
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
	public function deleteForms($formElements)
	{
		foreach ($formElements as $key => $formElement)
		{
			$form = SproutForms::$app->forms->getFormById($formElement->id);

			if ($form)
			{
				SproutForms::$app->forms->deleteForm($form);
			}
			else
			{
				SproutForms::log("Can't delete the form with id: ".$id);
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
		$form   = new FormElement();
		$name   = empty($name) ? 'Form' : $name ;
		$handle = empty($handle) ? 'form' : $handle;

		$form->name   = $this->getFieldAsNew('name', $name);
		$form->handle = $this->getFieldAsNew('handle', $handle);
		// Set default tab
		$field = null;
		$form  = SproutForms::$app->fields->addDefaultTab($form, $field);

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

}

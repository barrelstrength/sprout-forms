<?php
namespace Craft;

use Guzzle\Http\Client;

class SproutForms_EntriesService extends BaseApplicationComponent
{
	public $fakeIt = false;

	protected $entryRecord;

	/**
	 * @param object $entryRecord
	 */
	public function __construct($entryRecord = null)
	{
		$this->entryRecord = $entryRecord;

		if (is_null($this->entryRecord))
		{
			$this->entryRecord = SproutForms_EntryRecord::model();
		}
	}

	/**
	 * Returns a criteria model for SproutForms_Entry elements
	 *
	 * @param array $attributes
	 *
	 * @return ElementCriteriaModel
	 * @throws Exception
	 */
	public function getCriteria(array $attributes = array())
	{
		return craft()->elements->getCriteria('SproutForms_Entry', $attributes);
	}

	/**
	 * @param SproutForms_EntryModel $entry
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function forwardEntry(SproutForms_EntryModel &$entry)
	{
		if (!$entry->validate())
		{
			sproutForms()->log($entry->getErrors());

			return false;
		}

		// Setting the title explicitly to perform field validation
		$entry->getContent()->setAttribute('title', sha1(time()));

		$fields   = $entry->getPayloadFields();
		$endpoint = $entry->form->submitAction;

		if (empty($endpoint) || !filter_var($endpoint, FILTER_VALIDATE_URL))
		{
			sproutForms()->log('{form} has no submit action or submit action is an invalid URL', array('form' => $entry->formName));

			return false;
		}

		$client = new Client();

		// Annoying context switching
		$oldFieldContext = craft()->content->fieldContext;
		$oldContentTable = craft()->content->contentTable;

		craft()->content->fieldContext = $entry->getFieldContext();
		craft()->content->contentTable = $entry->getContentTable();

		$success = craft()->content->validateContent($entry);

		craft()->content->fieldContext = $oldFieldContext;
		craft()->content->contentTable = $oldContentTable;

		if (!$success)
		{
			$entry->addErrors($entry->getContent()->getErrors());

			sproutForms()->log($entry->getErrors());

			return false;
		}

		try
		{
			sproutForms()->log($fields);

			$response = $client->post($endpoint, null, $fields)->send();

			sproutForms()->log($response->getBody());

			return true;
		}
		catch (\Exception $e)
		{
			$entry->addError('general', $e->getMessage());

			return false;
		}
	}

	/**
	 * @param SproutForms_EntryModel $entry
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function saveEntry(SproutForms_EntryModel &$entry)
	{
		$isNewEntry = !$entry->id;

		if ($entry->id)
		{
			$entryRecord = SproutForms_EntryRecord::model()->findById($entry->id);

			if (!$entryRecord)
			{
				throw new Exception(Craft::t('No entry exists with id “{id}”', array('id' => $entry->id)));
			}
		}
		else
		{
			$entryRecord = new SproutForms_EntryRecord();
		}

		$entryRecord->formId    = $entry->formId;
		$entryRecord->ipAddress = $entry->ipAddress;
		$entryRecord->userAgent = $entry->userAgent;

		$entryRecord->validate();
		$entry->addErrors($entryRecord->getErrors());

		Craft::import('plugins.sproutforms.events.SproutForms_OnBeforeSaveEntryEvent');

		$event = new SproutForms_OnBeforeSaveEntryEvent(
			$this, array(
				'entry'      => $entry,
				'isNewEntry' => $isNewEntry
			)
		);

		craft()->sproutForms->onBeforeSaveEntry($event);

		if (!$entry->hasErrors())
		{

			try
			{
				$form = sproutForms()->forms->getFormById($entry->formId);

				$entry->getContent()->title = craft()->templates->renderObjectTemplate($form->titleFormat, $entry);

				$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

				if ($event->isValid)
				{
					$oldFieldContext = craft()->content->fieldContext;
					$oldContentTable = craft()->content->contentTable;

					craft()->content->fieldContext = $entry->getFieldContext();
					craft()->content->contentTable = $entry->getContentTable();

					SproutFormsPlugin::log('Transaction: Event is Valid');

					$success = craft()->elements->saveElement($entry);

					SproutFormsPlugin::log('Element Saved: '. $success);

					if ($success)
					{
						// Now that we have an element ID, save it on the other stuff
						if ($isNewEntry)
						{
							$entryRecord->id = $entry->id;
						}

						// Save our Entry Settings
						$entryRecord->save(false);

						if ($transaction !== null)
						{
							$transaction->commit();

							SproutFormsPlugin::log('Transaction committed');
						}

						// Reset our field context and content table to what they were previously
						craft()->content->fieldContext = $oldFieldContext;
						craft()->content->contentTable = $oldContentTable;

						Craft::import('plugins.sproutforms.events.SproutForms_OnSaveEntryEvent');

						$event = new SproutForms_OnSaveEntryEvent(
							$this, array(
								'entry'      => $entry,
								'isNewEntry' => $isNewEntry,
								'event'      => 'saveEntry',
								'entity'     => $entry,
							)
						);

						craft()->sproutForms->onSaveEntry($event);

						return true;
					}

					craft()->content->fieldContext = $oldFieldContext;
					craft()->content->contentTable = $oldContentTable;
				}
				else
				{
					SproutFormsPlugin::log('OnBeforeSaveEntryEvent is not valid', LogLevel::Error);

					if ($event->fakeIt)
					{
						sproutForms()->entries->fakeIt = true;
					}
				}
			}
			catch (\Exception $e)
			{
				SproutFormsPlugin::log('Failed to save element');
				return false;
				throw $e;
			}
		}
		else
		{
			SproutFormsPlugin::log('Service returns false');

			return false;
		}
	}

	/**
	 * Deletes an entry
	 *
	 * @param SproutForms_EntryModel $entry
	 *
	 * @throws \CDbException
	 * @throws \Exception
	 *
	 * @return bool
	 */
	public function deleteEntry(SproutForms_EntryModel $entry)
	{
		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		try
		{
			// Delete the Element and Entry
			craft()->elements->deleteElementById($entry->id);

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
	 * Returns an array of models for entries found in the database
	 *
	 * @return SproutForms_EntryModel|array|null
	 */
	public function getAllEntries()
	{
		$attributes = array('order' => 'name');

		return $this->getCriteria($attributes)->find();
	}

	/**
	 * Returns a form entry model if one is found in the database by id
	 *
	 * @param int $entryId
	 *
	 * @return null|SproutForms_EntryModel
	 */
	public function getEntryById($entryId)
	{
		return $this->getCriteria(array('limit' => 1, 'id' => $entryId))->first();
	}

	/**
	 * Returns an active or new entry model
	 *
	 * @param SproutForms_FormModel $form
	 *
	 * @return SproutForms_EntryModel
	 */
	public function getEntryModel(SproutForms_FormModel $form)
	{
		if (isset(sproutForms()->forms->activeEntries[$form->handle]))
		{
			return sproutForms()->forms->activeEntries[$form->handle];
		}

		$entry = new SproutForms_EntryModel;

		$entry->setAttribute('formId', $form->id);

		return $entry;
	}

	/**
	 * Updates previous title formats
	 *
	 * @param Mixed $contentRow
	 * @param String $newFormat
	 * @param String $contentTable
	 *
	 * @return boolean
	 */
	public function updateTitleFormat($contentRow, $newFormat, $contentTable)
	{
		try
		{
			// get the entry
			$entry       = sproutForms()->entries->getEntryById($contentRow['elementId']);
			// update the title with the new format
			$newTitle    = craft()->templates->renderObjectTemplate($newFormat, $entry);
			$tablePrefix = Craft()->db->tablePrefix;
			// update single entry
			Craft()->db->createCommand("UPDATE {$tablePrefix}{$contentTable} SET title =:newTitle WHERE id=:contentId")
				->bindValues(array(':contentId' => $contentRow['id'], ':newTitle' => $newTitle))
				->execute();
		}
		catch (Exception $e)
		{
			SproutFormsPlugin::log('An error has occurred: '.$e->getMessage(), LogLevel::Info, true);
			return false;
		}

		return true;
	}

	/**
	 * Get Content Entries
	 * @param String $contentTable
	 * @return boolean
	 */
	public function getContentEntries($contentTable)
	{
		$entries = Craft()->db->createCommand()
			->select('id, elementId')
			->from($contentTable)
			->queryAll();

		return $entries;
	}
}

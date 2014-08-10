<?php
namespace Craft;

class SproutForms_EntriesService extends BaseApplicationComponent
{
	protected $entryRecord;

	/**
	 * Constructor
	 * 
	 * @param object $entryRecord
	 */
	public function __construct($entryRecord = null)
	{
		$this->entryRecord = $entryRecord;
		if (is_null($this->entryRecord)) {
			$this->entryRecord = SproutForms_EntryRecord::model();
		}
	}
	
	/**
	 * Saves a entry.
	 *
	 * @param SproutForms_EntryModel $entry
	 * @throws \Exception
	 * @return bool
	 */
	public function saveEntry(SproutForms_EntryModel $entry)
	{	
		$isNewEntry = !$entry->id;

		if ($entry->id)
		{
			$entryRecord = SproutForms_EntryRecord::model()->findById($entry->id);

			if (!$entryRecord)
			{
				throw new Exception(Craft::t('No entry exists with the ID “{id}”', array('id' => $entry->id)));
			}

			$oldEntry = SproutForms_EntryModel::populateModel($entryRecord);
		}
		else
		{
			$entryRecord = new SproutForms_EntryRecord();
		}

		$entryRecord->formId = $entry->formId;
		$entryRecord->ipAddress = $entry->ipAddress;
		$entryRecord->userAgent = $entry->userAgent;

		$entryRecord->validate();
		$entry->addErrors($entryRecord->getErrors());

		if (!$entry->hasErrors())
		{
			$form = craft()->sproutForms_forms->getFormById($entry->formId);

			$entry->getContent()->title = craft()->templates->renderObjectTemplate($form->titleFormat, $entry);

			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				// Fire an 'onBeforeSaveEntry' event
				$this->onBeforeSaveEntry(new Event($this, array(
					'entry'      => $entry,
					'isNewEntry' => $isNewEntry
				)));

				if (craft()->elements->saveElement($entry))
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
					}

					// Fire an 'onSaveEntry' event
					$this->onSaveEntry(new Event($this, array(
						'entry'      => $entry,
						'isNewEntry' => $isNewEntry
					)));

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

		return false;
	}

	/**
	 * Delete Entry
	 * 
	 * @param int $id
	 * @return boolean
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
	 * Return entry by id
	 * 
	 * @param int $id
	 * @return object
	 */
	public function getEntryById($id)
	{
		$entryRecord = $this->entryRecord->findById($id);
		
		if ($entryRecord) 
		{
			return SproutForms_EntryModel::populateModel($entryRecord);
		} 
		else 
		{
			return null;
		}
	}

	// Events
	// ======

	/**
	 * Fires an 'onBeforeSaveEntry' event.
	 *
	 * @param Event $event
	 */
	public function onBeforeSaveEntry(Event $event)
	{
		$this->raiseEvent('onBeforeSaveEntry', $event);
	}

	/**
	 * Fires an 'onSaveEntry' event.
	 *
	 * @param Event $event
	 */
	public function onSaveEntry(Event $event)
	{
		$this->raiseEvent('onSaveEntry', $event);
	}
}
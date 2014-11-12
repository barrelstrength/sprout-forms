<?php
namespace Craft;

class SproutForms_EntriesService extends BaseApplicationComponent
{
	public $fakeIt = false;

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
	 * Returns a criteria model for SproutForms_Entry elements
	 *
	 * @param array $attributes
	 *
	 * @return ElementCriteriaModel
	 * @throws Exception
	 */
	public function getCriteria(array $attributes=array())
	{
		return craft()->elements->getCriteria('SproutForms_Entry', $attributes);
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

		
		// ------------------------------------------------------------
		// Fire 'onBeforeSaveEntry' Event
		// ------------------------------------------------------------
		
		Craft::import('plugins.sproutforms.events.SproutForms_OnBeforeSaveEntryEvent');

		$event = new SproutForms_OnBeforeSaveEntryEvent($this, array(
			'entry'      => $entry,
			'isNewEntry' => $isNewEntry
		));

		craft()->sproutForms->onBeforeSaveEntry($event);
		
		// ------------------------------------------------------------

		if (!$entry->hasErrors())
		{
			$form = craft()->sproutForms_forms->getFormById($entry->formId);

			$entry->getContent()->title = craft()->templates->renderObjectTemplate($form->titleFormat, $entry);

			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{	
				if ($event->isValid)
				{	
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

						// ------------------------------------------------------------
						// Fire an 'onSaveEntry' event
						// ------------------------------------------------------------
						
						Craft::import('plugins.sproutforms.events.SproutForms_OnSaveEntryEvent');
						
						$event = new SproutForms_OnSaveEntryEvent($this, array(
							'entry'      => $entry,
							'isNewEntry' => $isNewEntry,

							// @TODO - DEPRECATE and IMPROVE
							// Support for Sprout Email Event
							'event'      => 'saveEntry',
							'entity'     => $entry,
						));

						craft()->sproutForms->onSaveEntry($event);

						// ------------------------------------------------------------

						return true;
					}
				}
				else
				{	
					if ($event->fakeIt) 
					{
						// Pretend to submit the form even though it didn't submit
						craft()->sproutForms_entries->fakeIt = true;
					}
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
	 * Deletes an entry
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
	 * Returns an array of models for entries found in the database
	 *
	 * @return SproutForms_EntryModel|array|null
	 */
	public function getAllEntries()
	{
		$attributes	= array('order' => 'name');

		return $this->getCriteria($attributes)->find();
	}

	/**
	 * Returns a form entry model if one is found in the database by id
	 *
	 * @param int $entryId
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
		// If a form has been submitted, use our existing EntryModel
		// otherwise, create a new EntryModel
		if (isset(craft()->sproutForms_forms->activeEntries[$form->handle]))
		{	
			return craft()->sproutForms_forms->activeEntries[$form->handle];
		}

		$entry = new SproutForms_EntryModel;

		$entry->setAttribute('formId', $form->id);

		return $entry;
	}
}

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
		$entryRecord->statusId  = $entry->statusId != null ? $entry->statusId : $this->getDefaultEntryStatusId();

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

					if ($success)
					{
						SproutFormsPlugin::log('Element Saved: ' . $success);
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

						$this->callOnSaveEntryEvent($entry, $isNewEntry);

						return true;
					}
					else
					{
						SproutFormsPlugin::log("Couldn’t save Element on saveEntry service.", LogLevel::Error, true);
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
				SproutFormsPlugin::log('Failed to save element: '.$e->getMessage());

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

	public function callOnSaveEntryEvent($entry, $isNewEntry)
	{
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
		$attributes = array('order' => 'forms.name');

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
		$entry = null;

		if ($entryId)
		{
			$entry = $this->getCriteria(array('limit' => 1, 'id' => $entryId))->first();
		}

		return $entry;
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
	 * @param Mixed  $contentRow
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
			$entry = sproutForms()->entries->getEntryById($contentRow['elementId']);
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
			SproutFormsPlugin::log('An error has occurred: ' . $e->getMessage(), LogLevel::Info, true);

			return false;
		}

		return true;
	}

	/**
	 * Get Content Entries
	 *
	 * @param String $contentTable
	 *
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

	public function installDefaultEntryStatuses()
	{
		$defaultEntryStatuses = array(
			0 => array(
				'name'      => 'Unread',
				'handle'    => 'unread',
				'color'     => 'blue',
				'sortOrder' => 1,
				'isDefault' => 1
			),
			1 => array(
				'name'      => 'Read',
				'handle'    => 'read',
				'color'     => 'grey',
				'sortOrder' => 2,
				'isDefault' => 0
			)
		);

		foreach ($defaultEntryStatuses as $entryStatus)
		{
			craft()->db->createCommand()->insert('sproutforms_entrystatuses', array(
				'name'      => $entryStatus['name'],
				'handle'    => $entryStatus['handle'],
				'color'     => $entryStatus['color'],
				'sortOrder' => $entryStatus['sortOrder'],
				'isDefault' => $entryStatus['isDefault']
			));
		}
	}

	/**
	 * @return array
	 */
	public function getAllEntryStatuses()
	{
		$entryStatuses = craft()->db->createCommand()
			->select('*')
			->from('sproutforms_entrystatuses')
			->order('sortOrder asc')
			->queryAll();

		return SproutForms_EntryStatusModel::populateModels($entryStatuses);
	}

	/**
	 * @param $entryStatusId
	 *
	 * @return BaseModel
	 */
	public function getEntryStatusById($entryStatusId)
	{
		$entryStatus = craft()->db->createCommand()
			->select('*')
			->from('sproutforms_entrystatuses')
			->where('id=:id', array(':id' => $entryStatusId))
			->queryRow();

		return $entryStatus != null ? SproutForms_EntryStatusModel::populateModel($entryStatus) : null;
	}

	/**
	 * @param SproutForms_EntryStatusModel $entryStatus
	 *
	 * @return bool
	 * @throws Exception
	 * @throws \CDbException
	 * @throws \Exception
	 */
	public function saveEntryStatus(SproutForms_EntryStatusModel $entryStatus)
	{
		$record = new SproutForms_EntryStatusRecord;

		if ($entryStatus->id)
		{
			$record = SproutForms_EntryStatusRecord::model()->findByPk($entryStatus->id);

			if (!$record)
			{
				throw new Exception(Craft::t('No Entry Status exists with the id of “{id}”', array('id' => $entryStatus->id)));
			}
		}

		$record->setAttributes($entryStatus->getAttributes(), false);

		$record->sortOrder = $entryStatus->sortOrder ? $entryStatus->sortOrder : 999;

		$record->validate();

		$entryStatus->addErrors($record->getErrors());

		if (!$entryStatus->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				if ($record->isDefault)
				{
					SproutForms_EntryStatusRecord::model()->updateAll(array('isDefault' => 0));
				}

				$record->save(false);

				if (!$entryStatus->id)
				{
					$entryStatus->id = $record->id;
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

		return false;
	}

	/**
	 * @param int
	 *
	 * @return bool
	 */
	public function deleteEntryStatusById($id)
	{
		$statuses = $this->getAllEntryStatuses();

		$criteria = craft()->elements->getCriteria('SproutForms_Entry');
		$criteria->statusId = $id;
		$order = $criteria->first();

		if ($order)
		{
			return false;
		}

		if (count($statuses) >= 2)
		{
			SproutForms_EntryStatusRecord::model()->deleteByPk($id);

			return true;
		}

		return false;
	}

	/**
	 * Reorders Entry Statuses
	 *
	 * @param array $entryStatusIds
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function reorderEntryStatuses($entryStatusIds)
	{
		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		try
		{
			foreach ($entryStatusIds as $entryStatus => $entryStatusId)
			{
				$entryStatusRecord            = $this->_getEntryStatusRecordById($entryStatusId);
				$entryStatusRecord->sortOrder = $entryStatus + 1;
				$entryStatusRecord->save();
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

	public function getDefaultEntryStatusId()
	{
		$entryStatus = SproutForms_EntryStatusRecord::model()->find(array('order'=>'isDefault DESC'));

		return $entryStatus != null ? $entryStatus->id : null;
	}

	public function isDataSaved($form)
	{
		$settings = craft()->plugins->getPlugin('sproutforms')->getSettings();

		$saveData = $settings['enableSaveData'];

		if (($settings['enableSaveDataPerFormBasis'] && $saveData) || $form->submitAction)
		{
			$saveData = $form->saveData;
		}

		return $saveData;
	}

	/**
	 *@return null|HttpException
	*/
	public function userCanViewEntries()
	{
		if (!craft()->userSession->checkPermission('viewSproutFormsEntries'))
		{
			throw new HttpException(401, Craft::t("Not authorized to view Form Entries."));
		}
	}

	/**
	 *@return null|HttpException
	*/
	public function userCanEditEntries()
	{
		if (!craft()->userSession->checkPermission('editSproutFormsEntries'))
		{
			throw new HttpException(401, Craft::t("Not authorized to edit Form Entries."));
		}
	}

	/**
	 * Gets an Entry Status's record.
	 *
	 * @param int $sourceId
	 *
	 * @throws Exception
	 * @return AssetSourceRecord
	 */
	private function _getEntryStatusRecordById($entryStatusId = null)
	{
		if ($entryStatusId)
		{
			$entryStatusRecord = SproutForms_EntryStatusRecord::model()->findById($entryStatusId);

			if (!$entryStatusRecord)
			{
				throw new Exception(Craft::t('No Entry Status exists with the ID “{id}”.', array('id' => $entryStatusId)));
			}
		}
		else
		{
			$entryStatusRecord = new SproutForms_EntryStatusRecord();
		}

		return $entryStatusRecord;
	}
}

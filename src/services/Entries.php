<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use Guzzle\Http\Client;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SproutBaseRelationField;
use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;
use craft\base\ElementInterface;
use yii\base\Component;

class Entries extends Component
{
	public $fakeIt = false;
	protected $entryRecord;


	/**
	 * @param EntryRecord $entryRecord
	 */
	public function __construct($entryRecord = null)
	{
		$this->entryRecord = $entryRecord;

		if (is_null($this->entryRecord))
		{
			$this->entryRecord = EntryRecord::find();
		}
	}

	/**
	 * Returns an active or new entry element
	 *
	 * @param SproutForms_FormModel $form
	 *
	 * @return EntryElement
	 */
	public function getEntry(FormElement $form)
	{
		if (isset(SproutForms::$app->forms->activeEntries[$form->handle]))
		{
			return SproutForms::$app->forms->activeEntries[$form->handle];
		}

		$entry = new EntryElement;

		$entry->formId = $form->id;

		return $entry;
	}

	/**
	 * @param $entryStatusId
	 *
	 * @return BaseModel
	 */
	public function getEntryStatusById($entryStatusId)
	{
		$entryStatus = EntryStatusRecord::find()
			->where(['id' => $entryStatusId])
			->one();

		return $entryStatus != null ? $entryStatus : null;
	}

	/**
	 * @param EntryStatus $entryStatus
	 *
	 * @return bool
	 * @throws Exception
	 * @throws \CDbException
	 * @throws \Exception
	 */
	public function saveEntryStatus(EntryStatus $entryStatus): bool
	{
		$record = new SproutForms_EntryStatusRecord;

		if ($entryStatus->id)
		{
			$record = EntryStatusRecord::findOne($entryStatus->id);

			if (!$record)
			{
				throw new \Exception(SproutForms::t('No Entry Status exists with the id of “{id}”', array('id' => $entryStatus->id)));
			}
		}

		$record->setAttributes($entryStatus->getAttributes(), false);

		$record->sortOrder = $entryStatus->sortOrder ? $entryStatus->sortOrder : 999;

		$record->validate();

		$entryStatus->addErrors($record->getErrors());

		if (!$entryStatus->hasErrors())
		{
			$transaction = Craft::$app->db->beginTransaction();

			try
			{
				if ($record->isDefault)
				{
					EntryStatusRecord::updateAll(['isDefault' => 0]);
				}

				$record->save(false);

				if (!$entryStatus->id)
				{
					$entryStatus->id = $record->id;
				}

				$transaction->commit();
			}
			catch (\Exception $e)
			{
				$transaction->rollback();

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

		$entry = EntryElement::find()->where(['statusId'=>$id])->one();

		if ($entry)
		{
			return false;
		}

		if (count($statuses) >= 2)
		{
			$entryStatus = EntryStatusRecord::findOne($id);

			if ($entryStatus)
			{
				$entryStatus->delete();
				return true;
			}
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
		$transaction = Craft::$app->db->beginTransaction();

		try
		{
			foreach ($entryStatusIds as $entryStatus => $entryStatusId)
			{
				$entryStatusRecord            = $this->_getEntryStatusRecordById($entryStatusId);
				$entryStatusRecord->sortOrder = $entryStatus + 1;
				$entryStatusRecord->save();
			}

			$transaction->commit();
		}
		catch (\Exception $e)
		{
			$transaction->rollback();

			throw $e;
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function getAllEntryStatuses()
	{
		$entryStatuses = EntryStatusRecord::find()
			->orderBy(['sortOrder' => 'asc'])
			->all();

		return $entryStatuses;
	}

	/**
	 * Returns a form entry model if one is found in the database by id
	 *
	 * @param int $entryId
	 *
	 * @return null|EntryElement
	 */
	public function getEntryById($entryId, int $siteId = null)
	{
		$query = EntryElement::find();
		$query->id($entryId);
		$query->siteId($siteId);
		// @todo - research next function
		#$query->enabledForSite(false);

		return $query->one();
	}

	/**
	 * @param EntryElement $entry
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function saveEntry(EntryElement &$entry)
	{
		$isNewEntry = !$entry->id;

		$view = Craft::$app->getView();

		if ($entry->id)
		{
			$entryRecord = EntryRecord::findOne($entry->id);

			if (!$entryRecord)
			{
				throw new Exception(SproutForms::t('No entry exists with id '.$entry->id));
			}
		}

		$form            = SproutForms::$app->forms->getFormById($entry->formId);
		$entry->statusId = $entry->statusId != null ? $entry->statusId : $this->getDefaultEntryStatusId();
		$entry->title    = $view->renderObjectTemplate($form->titleFormat, $entry);

		$entry->validate();

		if ($entry->hasErrors())
		{
			SproutForms::error('Entry has errors');

			return false;
		}
		// EVENT_BEFORE_SAVE event moved to the element class https://github.com/craftcms/docs/blob/master/en/updating-plugins.md#events

		$event = new OnBeforeSaveEntryEvent([
			'entry' => $entry
		]);

		$this->trigger(EntryElement::EVENT_BEFORE_SAVE, $event);

		$db          = Craft::$app->getDb();
		$transaction = $db->beginTransaction();

		try
		{
			if (!$event->isValid)
			{
				SproutForms::error('OnBeforeSaveEntryEvent is not valid');

				if ($event->fakeIt)
				{
					SproutForms::$app->entries->fakeIt = true;
				}

				return false;
			}

			/* @todo - delete the context code after confirm that is not needed anymore on Craft3 behavior*/
			//$content         = Craft::$app->getContent();
			//$oldFieldContext = $content->fieldContext;
			//$oldContentTable = $content->contentTable;

			//$content->fieldContext = $entry->getFieldContext();
			//$content->contentTable = $entry->getContentTable();

			SproutForms::info('Transaction: Event is Valid');

			$success = Craft::$app->getElements()->saveElement($entry);

			// Reset our field context and content table to what they were previously
			//$content->fieldContext = $oldFieldContext;
			//$content->contentTable = $oldContentTable;

			if (!$success)
			{
				$transaction->rollBack();
				SproutForms::error("Couldn’t save Element on saveEntry service.");

				return false;
			}

			SproutForms::info('Element Saved!');

			$transaction->commit();

			SproutForms::info('Transaction committed');

			$event = new OnSaveEntryEvent([
				'entry' => $entry,
				'isNewEntry' => $isNewEntry,
			]);

			$this->trigger(EntryElement::EVENT_AFTER_SAVE, $event);
		}
		catch (\Exception $e)
		{
			SproutForms::error('Failed to save element: '.$e->getMessage());
			$transaction->rollBack();

			throw $e;
		}

		return true;
	}

	public function getDefaultEntryStatusId()
	{
		$entryStatus = EntryStatusRecord::find()
			->orderBy(['isDefault' => SORT_DESC])
			->one();

		return $entryStatus != null ? $entryStatus->id : null;
	}

	/**
	 * Saves some relations for a field.
	 *
	 * @param SproutBaseRelationField $field
	 * @param ElementInterface  $source
	 * @param array             $targetIds
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function saveRelations(SproutBaseRelationField $field, ElementInterface $source, array $targetIds)
	{
		/** @var Element $source */
		if (!is_array($targetIds)) {
			$targetIds = [];
		}

		// Prevent duplicate target IDs.
		$targetIds = array_unique($targetIds);

		$transaction = Craft::$app->getDb()->beginTransaction();

		try {
			// Delete the existing relations
			$oldRelationConditions = [
				'and',
				[
					'fieldId' => $field->id,
					'sourceId' => $source->id,
				]
			];

			if ($field->localizeRelations) {
				$oldRelationConditions[] = [
					'or',
					['sourceSiteId' => null],
					['sourceSiteId' => $source->siteId]
				];
			}

			Craft::$app->getDb()->createCommand()
				->delete('{{%relations}}', $oldRelationConditions)
				->execute();

			// Add the new ones
			if (!empty($targetIds)) {
				$values = [];

				if ($field->localizeRelations) {
					$sourceSiteId = $source->siteId;
				} else {
					$sourceSiteId = null;
				}

				foreach ($targetIds as $sortOrder => $targetId) {
					$values[] = [
						$field->id,
						$source->id,
						$sourceSiteId,
						$targetId,
						$sortOrder + 1
					];
				}

				$columns = [
					'fieldId',
					'sourceId',
					'sourceSiteId',
					'targetId',
					'sortOrder'
				];
				Craft::$app->getDb()->createCommand()
					->batchInsert('{{%relations}}', $columns, $values)
					->execute();
			}

			$transaction->commit();
		} catch (\Exception $e) {
			$transaction->rollBack();

			throw $e;
		}
	}

	/**
	 * Gets an Entry Status's record.
	 *
	 * @param int $sourceId
	 *
	 * @throws Exception
	 * @return EntryStatusRecord
	 */
	private function _getEntryStatusRecordById($entryStatusId = null)
	{
		if ($entryStatusId)
		{
			$entryStatusRecord = EntryStatusRecord::findOne($entryStatusId);

			if (!$entryStatusRecord)
			{
				throw new Exception(SproutForms::t('No Entry Status exists with the ID “{id}”.',
						['id' => $entryStatusId]
					)
				);
			}
		}
		else
		{
			$entryStatusRecord = new EntryStatusRecord();
		}

		return $entryStatusRecord;
	}

}

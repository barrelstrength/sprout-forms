<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SproutBaseRelationField;
use craft\base\ElementInterface;

use Guzzle\Http\Client;

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

}

<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;
use craft\events\ModelEvent;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;

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
		if (isset(SproutForms::$api->forms->activeEntries[$form->handle]))
		{
			return SproutForms::$api->forms->activeEntries[$form->handle];
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
	public function getEntryById($entryId)
	{
		return EntryRecord::findOne($entryId);
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

		$entry->statusId  = $entry->statusId != null ? $entry->statusId : $this->getDefaultEntryStatusId();

		$entry->validate();

		// EVENT_BEFORE_SAVE event moved to the element class https://github.com/craftcms/docs/blob/master/en/updating-plugins.md#events

		$event = new OnBeforeSaveEntryEvent([
			'entry' => $entry
		]);

		$this->trigger(EntryElement::EVENT_BEFORE_SAVE, $event);

		if (!$entry->hasErrors())
		{
			try
			{
				$form = SproutForms::$api->forms->getFormById($entry->formId);

				$entry->title = $view->renderObjectTemplate($form->titleFormat, $entry);

				$db          = Craft::$app->getDb();
				$transaction = $db->getTransaction() === null ? $db->beginTransaction() : null;

				if ($event->isValid)
				{
					$content         = Craft::$app->getContent();
					$oldFieldContext = $content->fieldContext;
					$oldContentTable = $content->contentTable;

					$content->fieldContext = $entry->getFieldContext();
					$content->contentTable = $entry->getContentTable();

					SproutForms::log('Transaction: Event is Valid');

					$success = Craft::$app->getElements()->saveElement($entry);

					if ($success)
					{
						SproutForms::log('Element Saved: ' . (bool)$success);

						if ($transaction !== null)
						{
							$transaction->commit();

							SproutForms::log('Transaction committed');
						}

						// Reset our field context and content table to what they were previously
						$content->fieldContext = $oldFieldContext;
						$content->contentTable = $oldContentTable;

						$event = new OnSaveEntryEvent([
							'entry' => $entry,
							'isNewEntry' => $isNewEntry,
						]);

						$this->trigger(EntryElement::EVENT_AFTER_SAVE, $event);

						return true;
					}
					else
					{
						SproutForms::log("Couldn’t save Element on saveEntry service.", 'error');
					}

					$content->fieldContext = $oldFieldContext;
					$content->contentTable = $oldContentTable;
				}
				else
				{
					SproutForms::log('OnBeforeSaveEntryEvent is not valid', 'error');

					if ($event->fakeIt)
					{
						SproutForms::$api->entries->fakeIt = true;
					}
				}
			}
			catch (\Exception $e)
			{
				SproutForms::log('Failed to save element: '.$e->getMessage(), 'error');

				return false;
				throw $e;
			}
		}
		else
		{
			SproutForms::log('Service returns false', 'error');

			return false;
		}
	}

	public function getDefaultEntryStatusId()
	{
		$entryStatus = EntryStatusRecord::find()
			->orderBy(['isDefault' => SORT_DESC])
			->one();

		return $entryStatus != null ? $entryStatus->id : null;
	}

}

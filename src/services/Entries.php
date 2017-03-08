<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;

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

		return $entryStatus != null ? SproutForms_EntryStatusModel::populateModel($entryStatus) : null;
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

}

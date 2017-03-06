<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\records\Entry as EntryRecord;

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
	 * @param $entryStatusId
	 *
	 * @return BaseModel
	 */
	public function getEntryStatusById($entryStatusId)
	{
		$entryStatus = Craft::$app->getDb()->createCommand()
			->from('{{%sproutforms_entrystatuses}}')
			->where(['id' => $entryStatusId])
			->one();

		return $entryStatus != null ? SproutForms_EntryStatusModel::populateModel($entryStatus) : null;
	}

	/**
	 * @return array
	 */
	public function getAllEntryStatuses()
	{
		$entryStatuses = Craft::$app->getDb()->createCommand()
			->from('{{%sproutforms_entrystatuses}}')
			->order('sortOrder asc')
			->all();

		return $entryStatuses;
	}

}

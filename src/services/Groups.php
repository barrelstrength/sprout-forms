<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\models\Form as FormModel;
use barrelstrength\sproutforms\models\FormGroup as FormGroupModel;
use barrelstrength\sproutforms\records\FormGroup as FormGroupRecord;

class Groups extends Component
{
	private $_groupsById;
	private $_fetchedAllGroups = false;

	/**
	 * Saves a group
	 *
	 * @param FormGroupModel $group
	 *
	 * @return bool
	 */
	public function saveGroup(FormGroupModel $group): bool
	{
		$groupRecord       = $this->_getGroupRecord($group);
		$groupRecord->name = $group->name;

		if ($groupRecord->validate())
		{
			$groupRecord->save(false);

			// Now that we have an ID, save it on the model & models
			if (!$group->id)
			{
				$group->id = $groupRecord->id;
			}

			return true;
		}
		else
		{
			$group->addErrors($groupRecord->getErrors());

			return false;
		}
	}

	/**
	 * Deletes a group
	 *
	 * @param int $groupId
	 *
	 * @return bool
	 */
	public function deleteGroupById($groupId)
	{
		$groupRecord = FormGroupRecord::find()->findOne($groupId);

		if (!$groupRecord)
		{
			return false;
		}

		$affectedRows = Craft::$app->getDb()
			->createCommand()
			->delete('{{%sproutforms_formgroups}}', ['id' => $groupId])
			->execute();

		return (bool) $affectedRows;
	}

	/**
	 * Returns all groups.
	 *
	 * @param string|null $indexBy
	 *
	 * @return array
	 */
	public function getAllFormGroups($indexBy = null)
	{
		if (!$this->_fetchedAllGroups)
		{
			$groupRecords = FormGroupRecord::find()
				->orderBy(['name' => SORT_ASC])
				->all();

			foreach ($groupRecords as $key => $groupRecord)
			{
				$groupRecords[$key] = new FormGroupModel($groupRecord);
			}

			$this->_groupsById       = $groupRecords;
			$this->_fetchedAllGroups = true;
		}

		if ($indexBy == 'id')
		{
			$groups = $this->_groupsById;
		}
		else
		{
			if (!$indexBy)
			{
				$groups = array_values($this->_groupsById);
			}
			else
			{
				$groups = array();
				foreach ($this->_groupsById as $group)
				{
					$groups[$group->$indexBy] = $group;
				}
			}
		}

		return $groups;
	}

	/**
	 * Get Forms by Group ID
	 *
	 * @param  int $groupId
	 *
	 * @return SproutForms_FormModel
	 */
	public function getFormsByGroupId($groupId)
	{
		$query = Craft::$app->getDb()
			->createCommand()
			->from('{{%sproutforms_formgroups}}')
			->where('groupId=:groupId', ['groupId' => $groupId])
			->order('name')
			->all();

		foreach ($query as $key => $value)
		{
			$query[$key] = new FormModel($value);
		}

		return $query;
	}

	/**
	 * Gets a form group record or creates a new one.
	 *
	 * @access private
	 *
	 * @param FormGroupModel $group
	 *
	 * @throws Exception
	 * @return FormGroupRecord
	 */
	private function _getGroupRecord(FormGroupModel $group)
	{
		if ($group->id)
		{
			$groupRecord = FormGroupRecord::find()->findOne($group->id);

			if (!$groupRecord)
			{
				throw new Exception(
					SproutForms::t(
						'No field group exists with the ID “{id}”',
						['id' => $group->id]
					)
				);
			}
		}
		else
		{
			$groupRecord = new FormGroupRecord();
		}

		return $groupRecord;
	}
}

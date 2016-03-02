<?php
namespace Craft;

class SproutForms_GroupsService extends BaseApplicationComponent
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
	public function saveGroup(SproutForms_FormGroupModel $group)
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
		$groupRecord = SproutForms_FormGroupRecord::model()->findById($groupId);

		if (!$groupRecord)
		{
			return false;
		}

		$affectedRows = craft()->db->createCommand()->delete('sproutforms_formgroups', array('id' => $groupId));

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
			$groupRecords            = SproutForms_FormGroupRecord::model()->ordered()->findAll();
			$this->_groupsById       = SproutForms_FormGroupModel::populateModels($groupRecords, 'id');
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
		$query = craft()->db->createCommand()
			->from('sproutforms_forms')
			->where('groupId=:groupId', array('groupId' => $groupId))
			->order('name')
			->queryAll();

		return SproutForms_FormModel::populateModels($query);
	}

	/**
	 * Gets a group record or creates a new one.
	 *
	 * @access private
	 *
	 * @param FormGroupModel $group
	 *
	 * @throws Exception
	 * @return FormGroupRecord
	 */
	private function _getGroupRecord(SproutForms_FormGroupModel $group)
	{
		if ($group->id)
		{
			$groupRecord = SproutForms_FormGroupRecord::model()->findById($group->id);

			if (!$groupRecord)
			{
				throw new Exception(Craft::t('No field group exists with the ID “{id}”', array('id' => $group->id)));
			}
		}
		else
		{
			$groupRecord = new SproutForms_FormGroupRecord();
		}

		return $groupRecord;
	}
}
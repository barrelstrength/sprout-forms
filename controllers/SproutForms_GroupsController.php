<?php
namespace Craft;

class SproutForms_GroupsController extends BaseController
{
	/**
	 * Save a group.
	 */
	public function actionSaveGroup()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$group       = new SproutForms_FormGroupModel();
		$group->id   = craft()->request->getPost('id');
		$group->name = craft()->request->getRequiredPost('name');

		$isNewGroup = empty($group->id);

		if (sproutForms()->groups->saveGroup($group))
		{
			if ($isNewGroup)
			{
				craft()->userSession->setNotice(Craft::t('Group added.'));
			}

			$this->returnJson(array(
				'success' => true,
				'group'   => $group->getAttributes(),
			));
		}
		else
		{
			$this->returnJson(array(
				'errors' => $group->getErrors(),
			));
		}
	}

	/**
	 * Deletes a group.
	 */
	public function actionDeleteGroup()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$groupId = craft()->request->getRequiredPost('id');
		$success = sproutForms()->groups->deleteGroupById($groupId);

		craft()->userSession->setNotice(Craft::t('Group deleted.'));

		$this->returnJson(array(
			'success' => $success,
		));
	}
}
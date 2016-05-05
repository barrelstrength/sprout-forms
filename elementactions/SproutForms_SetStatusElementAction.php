<?php
namespace Craft;

class SproutForms_SetStatusElementAction extends BaseElementAction
{
	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc IElementAction::getTriggerHtml()
	 *
	 * @return string|null
	 */
	public function getTriggerHtml()
	{
		return craft()->templates->render('sproutforms/_setStatus/trigger');
	}

	/**
	 * @inheritDoc IElementAction::performAction()
	 *
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return bool
	 */
	public function performAction(ElementCriteriaModel $criteria)
	{
		$status = $this->getParams()->status;
		//Unread by default
		$sqlStatus = 1;

		switch ($status)
		{
			case SproutForms_EntryModel::UNREAD:
				$sqlStatus = '1';
				break;
			case SproutForms_EntryModel::READ:
				$sqlStatus = '2';
				break;
			case SproutForms_EntryModel::ARCHIVED:
				$sqlStatus = '3';
				break;
		}

		$elementIds = $criteria->ids();

		// Update their statuses
		craft()->db->createCommand()->update(
			'sproutforms_entries',
			array('status' => $sqlStatus),
			array('in', 'id', $elementIds)
		);

		// Clear their template caches
		craft()->templateCache->deleteCachesByElementId($elementIds);

		// Fire an 'onSetStatus' event
		$this->onSetStatus(new Event($this, array(
			'criteria'   => $criteria,
			'elementIds' => $elementIds,
			'status'     => $status,
		)));

		$this->setMessage(Craft::t('Status updated.'));

		return true;
	}

	// Events
	// -------------------------------------------------------------------------

	/**
	 * Fires an 'onSetStatus' event.
	 *
	 * @param Event $event
	 *
	 * @return null
	 */
	public function onSetStatus(Event $event)
	{
		$this->raiseEvent('onSetStatus', $event);
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritDoc BaseElementAction::defineParams()
	 *
	 * @return array
	 */
	protected function defineParams()
	{
		return array(
			'status' => array(
				AttributeType::Enum,
				'values'   => array(
					SproutForms_EntryModel::UNREAD,
					SproutForms_EntryModel::READ,
					SproutForms_EntryModel::ARCHIVED
				),
				'required' => true)
		);
	}
}

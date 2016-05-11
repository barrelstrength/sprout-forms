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

		$elementIds = $criteria->ids();

		// Update their statuses
		craft()->db->createCommand()->update(
			'sproutforms_entries',
			array('statusId' => $status),
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
			'status' => AttributeType::Number,
		);
	}
}

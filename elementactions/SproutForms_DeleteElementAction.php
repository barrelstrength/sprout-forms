<?php
namespace Craft;

class SproutForms_DeleteElementAction extends BaseElementAction
{
	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc IComponentType::getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Delete..');
	}

	/**
	 * @inheritDoc IElementAction::isDestructive()
	 *
	 * @return bool
	 */
	public function isDestructive()
	{
		return true;
	}

	/**
	 * @inheritDoc IElementAction::getConfirmationMessage()
	 *
	 * @return string|null
	 */
	public function getConfirmationMessage()
	{
		return Craft::t('Are you sure you want to delete the selected forms?');
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
		$formsIds = $criteria->ids();

		$response = false;
		$message  = null;
		// Call deleteForms service
		$response = sproutForms()->forms->deleteForms($formsIds);

		if ($response)
		{
			$message = Craft::t('Forms Deleted.');
		}
		else
		{
			$message = Craft::t('Failed to delete forms.');
		}

		$this->setMessage($message);

		return $response;
	}

	/**
	 * @inheritDoc BaseElementAction::defineParams()
	 *
	 * @return array
	 */
	protected function defineParams()
	{
		return array();
	}
}

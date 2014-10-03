<?php
namespace Craft;

class SproutFormsService extends BaseApplicationComponent
{
	// Events
	// ======

	/**
	 * Fires an 'onBeforeSaveEntry' event.
	 *
	 * @param Event $event
	 */
	public function onBeforeSaveEntry(SproutForms_OnBeforeSaveEntryEvent $event)
	{
		$this->raiseEvent('onBeforeSaveEntry', $event);
	}

	/**
	 * Fires an 'onSaveEntry' event.
	 *
	 * @param Event $event
	 */
	public function onSaveEntry(SproutForms_OnSaveEntryEvent $event)
	{
		$this->raiseEvent('onSaveEntry', $event);
	}
}
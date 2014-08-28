<?php
namespace Craft;

class SproutFormsService extends BaseApplicationComponent
{
	// We're handing off our events to this function so we can keep the syntax
	// simple for our users.  Instead of sproutForms_entries.beforeSaveEntry we
	// can now have an event called sproutForms.beforeSaveEntry
	// 
	// The event can be called in the init() function of another plugin like so:
	// 
	// craft()->on('sproutForms_entries.beforeSaveEntry', array(
	// 	$this,
	// 	'myFunction' 
	// ));
	public function sproutRaiseEvent($eventHandle, $currentObject, $variables)
	{
		$this->{$eventHandle}(new Event($currentObject, $variables));
	}

	// Events
	// ======

	/**
	 * Fires an 'onBeforeSaveEntry' event.
	 *
	 * @param Event $event
	 */
	public function onBeforeSaveEntry(Event $event)
	{
		$this->raiseEvent('onBeforeSaveEntry', $event);
	}

	/**
	 * Fires an 'onSaveEntry' event.
	 *
	 * @param Event $event
	 */
	public function onSaveEntry(Event $event)
	{
		$this->raiseEvent('onSaveEntry', $event);
	}
}
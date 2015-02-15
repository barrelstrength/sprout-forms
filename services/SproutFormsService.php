<?php
namespace Craft;

class SproutFormsService extends BaseApplicationComponent
{
	public $entries;
	public $fields;
	public $forms;
	public $groups;
	public $settings;

	public function init()
	{
		parent::init();

		$this->entries  = Craft::app()->getComponent('sproutForms_entries');
		$this->fields   = Craft::app()->getComponent('sproutForms_fields');
		$this->forms    = Craft::app()->getComponent('sproutForms_forms');
		$this->groups   = Craft::app()->getComponent('sproutForms_groups');
		$this->settings = Craft::app()->getComponent('sproutForms_settings');
	}

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
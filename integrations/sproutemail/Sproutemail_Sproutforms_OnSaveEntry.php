<?php   
// MUST be in the Craft namespace
namespace Craft;  

class Sproutemail_Sproutforms_OnSaveEntry
{	
	/**
	 * This is the hook Sproutemail will call to register this event with Commerce
	 *
	 * @return string
	 */
	public function getHook()
	{
		return 'sproutformsAddEventListener';
	}
	
	/**
	 * Event name
	 *
	 * @return string
	 */
	public function getEvent()
	{
		return 'saveEntry';
	}
	
	/**
	 * Event name
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('SproutForms: On Save Entry (after submission)');
	}
	
	/**
	 * Event description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return Craft::t('This event will fire after an entry has been submited and saved.');
	}
	
	/**
	 * Display custom Commerce options
	 * 
	 * @return string Returns the template which displays our settings
	 */
	public function getOptionsHtml()
	{
		return '';
	}
	
	/**
	 * Process the options associated with the event
	 *
	 * @return string
	 */
	public function processOptions($event, $entity, $options)
	{

		return true;
	
	}
}
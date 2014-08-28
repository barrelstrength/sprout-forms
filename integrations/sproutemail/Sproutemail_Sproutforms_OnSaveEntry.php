<?php
namespace Craft;

class SproutEmail_SproutForms_OnSaveEntry
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
		return Craft::t('This event will fire after a form has been submited and saved.');
	}
	
	/**
	 * Display custom Commerce options
	 * 
	 * @return string Returns the template which displays our settings
	 */
	public function getOptionsHtml()
	{
		return '<hr>
			<h3>Custom options for SproutForms.</h3>

			{% set availableForms = [] %}
			
			{% for availableForm in craft.sproutforms.getAllForms() %}
				{% set availableForms = availableForms|merge({ (availableForm.handle) : availableForm.name}) %}
			{% endfor %}
			
			{% if campaign.notificationEvents[0] is defined %}
				{% set opts = campaign.notificationEvents[0].options %}
			{% endif %}
							
			{% if(availableForms|length > 0) %}
			
				{{ forms.field({
					label: "Forms"|t,
					instructions: "Select one or more forms.  If none selected, notification will apply to all."|t,
				}, forms.checkboxGroup({
					name: "options[sproutForms]",   
					options : availableForms,
					values: (opts.sproutForms is defined ? opts.sproutForms : null)
				})) }}
				
			{% else %}
				
				<p>You do not currently have any forms available.</p>
			
			{% endif %}
			<hr>';
	}
	
	/**
	 * Process the options associated with the event
	 *
	 * @return string
	 */
	public function processOptions($event, $entity, $options)
	{
		if (!is_array($options['options']['sproutForms']))
		{
			return true;
		}
		
		$submittedForm = craft()->request->getPost('handle');
		if ($submittedForm && in_array($submittedForm, $options['options']['sproutForms']))
		{
			return true;
		}
		
		return false;
		
	}
}
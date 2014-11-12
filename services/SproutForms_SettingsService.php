<?php
namespace Craft;

class SproutForms_SettingsService extends BaseApplicationComponent
{
	public function saveSettings($settings)
	{		
		$settings = JsonHelper::encode($settings);

		$affectedRows = craft()->db->createCommand()->update('plugins', array(
			'settings' => $settings
		), array(
			'class' => 'SproutForms'
		));

		return (bool) $affectedRows;
	}
}
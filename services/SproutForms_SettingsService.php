<?php
namespace Craft;

class SproutForms_SettingsService extends BaseApplicationComponent
{
	public function saveSettings($postSettings)
	{
		if (isset($postSettings['toggleTemplateFolderOverride']) && $postSettings['toggleTemplateFolderOverride'] != 1)
		{
			$postSettings['templateFolderOverride'] = '';
		}

		$plugin   = craft()->plugins->getPlugin('sproutforms');
		$settings = $plugin->getSettings();

		if (isset($postSettings['templateFolderOverride']))
		{
			$settings['templateFolderOverride'] = $postSettings['templateFolderOverride'];
		}

		if (isset($postSettings['pluginNameOverride']))
		{
			$settings['pluginNameOverride'] = $postSettings['pluginNameOverride'];
		}

		if (isset($postSettings['enablePerFormTemplateFolderOverride']))
		{
			$settings['enablePerFormTemplateFolderOverride'] = $postSettings['enablePerFormTemplateFolderOverride'];
		}

		if (isset($postSettings['enablePayloadForwarding']))
		{
			$settings['enablePayloadForwarding'] = $postSettings['enablePayloadForwarding'];
		}

		if (isset($postSettings['enableSaveData']))
		{
			$settings['enableSaveData'] = $postSettings['enableSaveData'];
		}

		if (isset($postSettings['enableSaveDataPerFormBasis']))
		{
			$settings['enableSaveDataPerFormBasis'] = $postSettings['enableSaveDataPerFormBasis'];
		}

		if (isset($postSettings['saveDataByDefault']))
		{
			$settings['saveDataByDefault'] = $postSettings['saveDataByDefault'];
		}

		$settings = JsonHelper::encode($settings);

		$affectedRows = craft()->db->createCommand()->update('plugins', array(
			'settings' => $settings
		), array(
			'class' => 'SproutForms'
		));

		return (bool) $affectedRows;
	}
}
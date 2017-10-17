<?php

namespace barrelstrength\sproutforms\models;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Model;

class Settings extends Model
{
	public $pluginNameOverride                  = '';
	public $templateFolderOverride              = '';
	public $enablePerFormTemplateFolderOverride = '0';
	public $enablePayloadForwarding             = '0';

	public function getSettingsNavItems()
	{
		return [
			'settingsHeading' => [
				'heading' => SproutForms::t('Settings'),
			],
			'general' => [
				'label' => SproutForms::t('General'),
				'url' => 'sprout-forms/settings/general',
				'selected' => 'general',
				'template' => 'sprout-forms/_settings/general'
			],
		];
	}
}
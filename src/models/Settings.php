<?php

namespace barrelstrength\sproutforms\models;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Model;

class Settings extends Model
{
	public $pluginNameOverride         = '';
	public $templateFolderOverride     = '';
	public $enablePayloadForwarding    = 0;
	public $enableSaveData             = 1;
	public $enableSaveDataPerFormBasis = 0;
	public $saveDataByDefault          = 1;
	public $enablePerFormTemplateFolderOverride = 0;

	public function getSettingsNavItems()
	{
		// Added new items override if needed
		// 'fullPageForm' => true,
	  // 'actionTemplate' => 'sprout-base/sproutbase/_includes/actionButton'
	  // 'actionUrl' => 'sprout-base/settings/save-settings'
		return [
			'general' => [
				'label' => SproutForms::t('General'),
				'url' => 'sprout-forms/settings/general',
				'selected' => 'general',
				'template' => 'sprout-forms/_settings/general'
			],
			'entry-statuses' => [
				'label' => SproutForms::t('Entry Statuses'),
				'url' => 'sprout-forms/settings/entry-statuses',
				'selected' => 'entry-statuses',
				'template' => 'sprout-forms/_settings/entrystatuses',
				'actionTemplate' => 'sprout-forms/_includes/actionStatusButton'
			],
			'advanced' => [
				'label' => SproutForms::t('Advanced'),
				'url' => 'sprout-forms/settings/advanced',
				'selected' => 'advanced',
				'template' => 'sprout-forms/_settings/general'
			],
			'settingsHeading' => [
				'heading' => SproutForms::t('Examples'),
			],
			'examples' => [
				'label' => SproutForms::t('Form Templates'),
				'url' => 'sprout-forms/settings/examples',
				'actionUrl' => 'sprout-forms/examples/install',
				'selected' => 'examples',
				'template' => 'sprout-forms/_settings/examples',
				'fullPageForm' => true,
				'actionTemplate' => 'sprout-forms/_includes/actionExampleButton'
			],
		];
	}
}
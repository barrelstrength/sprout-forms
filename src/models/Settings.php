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
				'template' => 'sprout-forms/_settings/entrystatuses'
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
			'form-templates' => [
				'label' => SproutForms::t('Form Templates'),
				'url' => 'sprout-forms/settings/form-templates',
				'selected' => 'form-templates',
				'template' => 'sprout-forms/_settings/examples'
			],
		];
	}
}
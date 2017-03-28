<?php
namespace Craft;

class SproutForms_SettingsModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'pluginNameOverride'                  => AttributeType::String,
			'templateFolderOverride'              => AttributeType::String,
			'enablePerFormTemplateFolderOverride' => AttributeType::Bool,
			'enablePayloadForwarding'             => AttributeType::Bool,
			'enableSaveData'                      => AttributeType::Bool,
			'enableSaveDataPerFormBasis'          => AttributeType::Bool,
			'saveDataByDefault'                   => AttributeType::Bool,
		);
	}
}
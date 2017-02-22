<?php

namespace barrelstrength\sproutforms\models;

class Settings extends \craft\base\Model
{
	public $pluginNameOverride                  = '';
	public $templateFolderOverride              = '';
	public $enablePerFormTemplateFolderOverride = '0';
	public $enablePayloadForwarding             = '0';
}
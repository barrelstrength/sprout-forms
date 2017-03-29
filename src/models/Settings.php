<?php

namespace barrelstrength\sproutforms\models;
use craft\base\Model;

class Settings extends Model
{
	public $pluginNameOverride                  = '';
	public $templateFolderOverride              = '';
	public $enablePerFormTemplateFolderOverride = '0';
	public $enablePayloadForwarding             = '0';
}
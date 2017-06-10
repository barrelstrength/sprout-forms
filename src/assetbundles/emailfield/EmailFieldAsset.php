<?php
namespace barrelstrength\sproutforms\assetbundles\emailfield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class EmailFieldAsset extends AssetBundle
{
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@barrelstrength/sproutforms/assetbundles/emailfield/resources/';

		// define the dependencies
		$this->depends = [
			CpAsset::class,
		];

		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'js/sproutemailfield.js',
		];

		$this->css = [
			'css/sproutfields.css',
		];

		parent::init();
	}
}
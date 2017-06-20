<?php
namespace barrelstrength\sproutforms\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class DragulaAsset extends AssetBundle
{
	public function init()
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@barrelstrength/sproutforms/resources';

		// define the dependencies
		$this->depends = [
			CpAsset::class,
		];

		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'dragula/dragula.min.js',
			'dragula/dom-autoscroller.min.js'
		];

		$this->css = [
			'dragula/dragula.min.css'
		];

		parent::init();
	}
}
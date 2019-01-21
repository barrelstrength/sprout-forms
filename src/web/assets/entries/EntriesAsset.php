<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutforms\web\assets\entries;

use barrelstrength\sproutforms\web\assets\charts\ChartsAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class EntriesAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@barrelstrength/sproutforms/web/assets/entries/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
            ChartsAsset::class
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/SproutFormsEntriesIndex.js',
            'js/SproutFormsEntriesTableView.js',
        ];

        parent::init();
    }
}
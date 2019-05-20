<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutforms\web\assets\integrations;

use barrelstrength\sproutforms\web\assets\charts\ChartsAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class IntegrationAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@barrelstrength/sproutforms/web/assets/integrations/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/Integration.js'
        ];

        parent::init();
    }
}
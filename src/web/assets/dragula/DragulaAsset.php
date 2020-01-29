<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\web\assets\dragula;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class DragulaAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutformslib/dragula';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/dragula.min.js',
            'js/dom-autoscroller.min.js'
        ];

        $this->css = [
            'css/dragula.min.css'
        ];

        parent::init();
    }
}
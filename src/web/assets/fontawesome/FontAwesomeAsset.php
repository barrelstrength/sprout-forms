<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\web\assets\fontawesome;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FontAwesomeAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutformslib/font-awesome';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'css/font-awesome.min.css'
        ];

        parent::init();
    }
}
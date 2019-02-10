<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutforms\web\assets\forms;

use barrelstrength\sproutbasefields\web\assets\selectother\SelectOtherFieldAsset;
use barrelstrength\sproutforms\web\assets\fontawesome\FontAwesomeAsset;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FormsAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@barrelstrength/sproutforms/web/assets/forms/dist';

        // define the dependencies
        // @todo - refactor sproutfields.js asset within SelectOtherField asset
        $this->depends = [
            CpAsset::class,
            FontAwesomeAsset::class,
            SelectOtherFieldAsset::class
        ];

        $this->css = [
            'css/forms.css'
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/FieldLayoutEditor.js',
            'js/FieldModal.js',
        ];

        parent::init();
    }
}
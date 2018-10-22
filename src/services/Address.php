<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutbase\app\fields\helpers\AddressHelper;
use Craft;

class Address extends AddressHelper
{
    public function renderTemplates($template, $params)
    {
        $html = Craft::$app->view->renderTemplate('address/_components/'.$template, $params);

        return $html;
    }
}

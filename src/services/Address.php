<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutbase\app\fields\helpers\AddressHelper;
use Craft;
use craft\helpers\UrlHelper;

class Address extends AddressHelper
{
    public function renderTemplates($template, $params)
    {
        $html = Craft::$app->view->renderTemplate('address/_components/'.$template, $params);

        return $html;
    }

    public function countryInput($hidden = null)
    {
        $countries = $this->getCountries();

        return $this->renderTemplates(
            'select',
            [
                'class' => 'sproutaddress-country-select',
                'label' => $this->renderHeading('Country'),
                'name' => $this->name.'[countryCode]',
                'inputName' => 'countryCode',
                'options' => $countries,
                'value' => $this->countryCode ?? $this->defaultCountryCode(),
                'nameValue' => $this->name,
                'hidden' => $hidden,
                'actionUrl' => UrlHelper::actionUrl('sprout-forms/address/change-form'),
                'addressInfo' => $this->addressModel
            ]
        );
    }
}

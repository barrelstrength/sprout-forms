<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbase\app\fields\helpers\AddressHelper;
use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Template as TemplateHelper;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\fields\models\Name as NameModel;
use barrelstrength\sproutforms\base\FormField;

class Address extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string
     */
    public $defaultCountry;

    /**
     * @var bool
     */
    public $hideCountryDropdown;

    /**
     * @var AddressHelper $addressHelper
     */
    protected $addressHelper;

    public function init()
    {
        $this->addressHelper = new AddressHelper();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Address');
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/map-marker-alt.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        $countries = $this->addressHelper->getCountries();
        $settings = $this->getSettings();

        if ($settings !== null && !isset($settings['defaultCountry']))
        {
            $settings['defaultCountry'] = 'US';
            $settings['country'] = 'US';
        }

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/address/settings',
            [
                'settings' => $settings,
                'countries' => $countries
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputName = Craft::$app->getView()->namespaceInputName($inputId);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $settings = $this->getSettings();

        $defaultCountryCode = $settings['defaultCountry'] ?? null;
        $hideCountryDropdown = $settings['hideCountryDropdown'] ?? null;

        $addressId = null;

        if (is_object($value)) {
            $addressId = $value->id;
        } elseif (is_array($value)) {
            $addressId = $value['id'];
        }

        $addressInfoModel = SproutBase::$app->addressField->getAddressById($addressId);

        $countryCode = $addressInfoModel->countryCode ?? $defaultCountryCode;

        $this->addressHelper->setParams($countryCode, $name, $addressInfoModel);

        $addressFormat = "";
        if ($addressId) {
            $addressFormat = $this->addressHelper->getAddressWithFormat($addressInfoModel);
        }

        $countryInput = $this->addressHelper->countryInput($hideCountryDropdown);
        $addressForm = $this->addressHelper->getAddressFormHtml();

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/address/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'namespaceInputName' => $namespaceInputName,
                'field' => $this,
                'addressId' => $addressId,
                'defaultCountryCode' => $defaultCountryCode,
                'addressFormat' => $addressFormat,
                'countryInput' => $countryInput,
                'addressForm' => $addressForm,
                'hideCountryDropdown' => $hideCountryDropdown
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/address/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param array|null $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'address/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * Prepare our Name for use as an NameModel
     *
     * @todo - move to helper as we can use this on both Sprout Forms and Sprout Fields
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return NameModel|mixed
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
//        $nameModel = new NameModel();
//
//        // String value when retrieved from db
//        if (is_string($value)) {
//            $nameArray = json_decode($value, true);
//            $nameModel->setAttributes($nameArray, false);
//        }
//
//        // Array value from post data
//        if (is_array($value) && isset($value['address'])) {
//
//            $nameModel->setAttributes($value['address'], false);
//
//            if ($fullNameShort = $value['address']['fullNameShort'] ?? null) {
//                $nameArray = explode(' ', trim($fullNameShort));
//
//                $nameModel->firstName = $nameArray[0] ?? $fullNameShort;
//                unset($nameArray[0]);
//
//                $nameModel->lastName = implode(' ', $nameArray);
//            }
//        }
//
//        return $nameModel;
    }

    /**
     *
     * Prepare the field value for the database.
     *
     * @todo - move to helper as we can use this on both Sprout Forms and Sprout Fields
     *
     * We store the Name as JSON in the content column.
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|bool|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
//        if (empty($value)) {
//            return false;
//        }
//
//        // Submitting an Element to be saved
//        if (is_object($value) && get_class($value) == NameModel::class) {
//            return json_encode($value->getAttributes());
//        }
//
//        return $value;
    }
}

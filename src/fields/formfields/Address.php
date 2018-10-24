<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbase\app\fields\helpers\AddressHelper as BaseAddressHelper;
use barrelstrength\sproutforms\services\Address as AddressHelper;
use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Template as TemplateHelper;
use barrelstrength\sproutbase\SproutBase;
use yii\db\Schema;
use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;
use CommerceGuys\Intl\Country\CountryRepository;

class Address extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string
     */
    public $defaultCountry = 'US';

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
        $this->addressHelper = new BaseAddressHelper();

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
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_INTEGER;
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
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return string
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
        $this->addressHelper = new AddressHelper();

        $name = $this->handle;

        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputName = Craft::$app->getView()->namespaceInputName($inputId);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $settings = $this->getSettings();

        $countryCode = $settings['defaultCountry'] ?? $this->defaultCountry;

        $hideCountryDropdown = $settings['hideCountryDropdown'] ?? null;

        $addressInfoModel = new AddressModel();

        $this->addressHelper->setParams($countryCode, $name, $addressInfoModel);

        $countryInput = $this->addressHelper->countryInput($hideCountryDropdown);

        $addressForm = $this->addressHelper->getAddressFormHtml();

        return Craft::$app->getView()->renderTemplate(
            'address/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'namespaceInputName' => $namespaceInputName,
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'form' => TemplateHelper::raw($addressForm),
                'countryInput' => TemplateHelper::raw($countryInput),
                'hideCountryDropdown' => $hideCountryDropdown
            ]
        );
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|AddressModel|int|mixed|string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $addressModel = new AddressModel();

        // Numeric value when retrieved from db
        if (is_numeric($value)) {
            $addressModel = SproutBase::$app->addressField->getAddressById($value);
        }

        // Array value from post data
        if (is_array($value)) {

            if (!empty($value['delete'])) {
                SproutBase::$app->addressField->deleteAddressById($value['id']);
            } else {
                $value['fieldId'] = $this->id ?? null;
                $addressModel = new AddressModel();
                $addressModel->setAttributes($value, false);
            }
        }

        // Adds country property that return country name
        if ($addressModel->countryCode) {
            $countryRepository = new CountryRepository();

            $country = $countryRepository->get($addressModel->countryCode);
            $addressModel->country = $country->getName();
        }

        // return null when clearing address to save null value on content table
        if (!$addressModel->validate(null, false)) {
            return $value;
        }

        return $addressModel;
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
       // \Craft::dump('serialize value');
        //\Craft::dump($value);
        if (empty($value)) {
            return false;
        }

        $addressId = null;

        // When loading a Field Layout with an Address Field
        if (is_object($value) && get_class($value) == AddressModel::class) {
            $addressId = $value->id;
        }

        // For the ResaveElements task $value is the id
        if (is_int($value)) {
            $addressId = $value;
        }

        // When the field is saved by post request the id an attribute on $value
        if (isset($value['id']) && $value['id']) {
            $addressId = $value['id'];
        }

        // Save the addressId in the content table
        return $addressId;
    }

    /**
     * Save our Address Field a first time and assign the Address Record ID back to the Address field model
     * We'll save our Address Field a second time in afterElementSave to capture the Element ID for new entries.
     *
     * @param ElementInterface $element
     * @param bool             $isNew
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function beforeElementSave(ElementInterface $element, bool $isNew) : bool
    {
        $address = $element->getFieldValue($this->handle);
        //\Craft::dump('before save');

        if ($address instanceof AddressModel)
        {
            $address->elementId = $element->id;
            $address->siteId = $element->siteId;
            $address->fieldId = $this->id;

            SproutBase::$app->addressField->saveAddress($address);
            //\Craft::dump($address);
        }

        return true;
    }

    /**
     * Save our Address Field a second time for New Entries to ensure we have the Element ID.
     *
     * @param ElementInterface $element
     * @param bool             $isNew
     *
     * @return bool|void
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        //\Craft::dump('after save');
        if ($isNew)
        {
            $address = $element->getFieldValue($this->handle);

            if ($address instanceof AddressModel)
            {
                $address->elementId = $element->id;
                SproutBase::$app->addressField->saveAddress($address);
            }
        }
    }

    public function getElementValidationRules(): array
    {

        return ['validateAddress'];
    }

    public function validateAddress(ElementInterface $element)
    {

    }

    public function rules()
    {
        return [];
    }
}

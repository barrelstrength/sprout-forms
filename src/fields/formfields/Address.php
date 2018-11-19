<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbase\app\fields\base\AddressFieldTrait;
use barrelstrength\sproutbase\app\fields\helpers\AddressHelper as BaseAddressHelper;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\services\Address as AddressHelper;
use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;
use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;

class Address extends FormField implements PreviewableFieldInterface
{
    use AddressFieldTrait;
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
    public $addressHelper;

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
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml()
    {
        $name = 'address';

        $settings = $this->getSettings();

        $defaultCountryCode = $settings['defaultCountry'] ?? null;
        $hideCountryDropdown = $settings['hideCountryDropdown'] ?? null;

        $addressId = null;

        $addressInfoModel = SproutBase::$app->addressField->getAddressById($addressId);

        $countryCode = $addressInfoModel->countryCode ?? $defaultCountryCode;

        $addressHelper = $this->addressHelper;

        /**
         * @var $addressHelper AddressHelper
         */
        $addressHelper->setParams($countryCode, $name, $addressInfoModel);

        $addressFormat = "";
        if ($addressId) {
            $addressFormat = $addressHelper->getAddressWithFormat($addressInfoModel);
        }

        $countryInput = $addressHelper->countryInput($hideCountryDropdown);

        $addressForm = $addressHelper->getAddressFormHtml();

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/address/input',
            [
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

        $csrfTokenName = Craft::$app->getConfig()->getGeneral()->csrfTokenName;

        return Craft::$app->getView()->renderTemplate(
            'address/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'namespaceInputName' => $namespaceInputName,
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'form' => TemplateHelper::raw($addressForm),
                'countryInput' => TemplateHelper::raw($countryInput),
                'hideCountryDropdown' => $hideCountryDropdown,
                'csrfTokenName' => $csrfTokenName
            ]
        );
    }

    public function getElementValidationRules(): array
    {
        return ['validateAddress'];
    }

    public function validateAddress(ElementInterface $element)
    {
        $values = $element->getFieldValue($this->handle);
        $addressInfoModel = new AddressModel($values);

        $addressInfoModel->validate();

        if ($addressInfoModel->hasErrors()) {
            $errors = $addressInfoModel->getErrors();

            if ($errors) {
                foreach ($errors as $error) {
                    $firstMessage = $error[0] ?? null;
                    $element->addError($this->handle, $firstMessage);
                }
            }

        }
    }

}

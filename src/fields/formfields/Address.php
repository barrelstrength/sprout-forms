<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbase\app\fields\base\AddressFieldTrait;
use barrelstrength\sproutbase\app\fields\helpers\AddressHelper as BaseAddressHelper;
use barrelstrength\sproutforms\services\Address as AddressHelper;
use Craft;
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
}

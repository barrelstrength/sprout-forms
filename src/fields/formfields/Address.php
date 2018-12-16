<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbase\app\fields\base\AddressFieldTrait;
use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use yii\db\Schema;
use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutbase\app\fields\models\Address as AddressModel;

/**
 *
 * @property array  $elementValidationRules
 * @property string $contentColumnType
 * @property string $svgIconPath
 * @property string $exampleInputHtml
 */
class Address extends FormField implements PreviewableFieldInterface
{
    use AddressFieldTrait;

    /**
     * @var string
     */
    public $cssClasses;

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
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/map-marker-alt.svg';
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
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
        $name = $this->handle;
        $settings = $this->getSettings();

        $countryCode = $settings['defaultCountry'] ?? $this->defaultCountry;
        $showCountryDropdown = $settings['showCountryDropdown'] ?? 0;

        $addressModel = new AddressModel();

        // This defaults to Sprout Base and we need it to get updated to look
        // in the Sprout Forms Form Template location like other fields.
        $this->addressHelper->setBaseAddressFieldPath('');

        $this->addressHelper->setNamespace($name);
        $this->addressHelper->setCountryCode($countryCode);
        $this->addressHelper->setAddressModel($addressModel);
        $this->addressHelper->setLanguage($this->defaultLanguage);

        $countryInputHtml = $this->addressHelper->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = $this->addressHelper->getAddressFormHtml();

        return Craft::$app->getView()->renderTemplate(
            'address/input', [
                'field' => $this,
                'name' => $this->handle,
                'renderingOptions' => $renderingOptions,

                'addressFormHtml' => Template::raw($addressFormHtml),
                'countryInputHtml' => Template::raw($countryInputHtml),
                'showCountryDropdown' => $showCountryDropdown,

                // For our country update requests
                'csrfTokenName' => Craft::$app->getConfig()->getGeneral()->csrfTokenName,
                'actionUrl' => UrlHelper::actionUrl('sprout/fields-address/update-address-form-html')
            ]
        );
    }

    public function getElementValidationRules(): array
    {
        return ['validateAddress'];
    }

    public function validateAddress(ElementInterface $element)
    {
        // @todo - improve validation
        if (!$this->required) {
            return true;
        }

        $values = $element->getFieldValue($this->handle);

        $addressModel = new AddressModel($values);
        $addressModel->validate();

        if ($addressModel->hasErrors()) {
            $errors = $addressModel->getErrors();

            if ($errors) {
                foreach ($errors as $error) {
                    $firstMessage = $error[0] ?? null;
                    $element->addError($this->handle, $firstMessage);
                }
            }
        }

        return true;
    }

}

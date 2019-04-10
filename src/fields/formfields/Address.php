<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbasefields\base\AddressFieldTrait;
use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;
use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutbasefields\models\Address as AddressModel;

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
    public function hasMultipleLabels(): bool
    {
        return true;
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
     * @return \Twig_Markup
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup
    {
        $name = $this->handle;
        $settings = $this->getSettings();

        $countryCode = $settings['defaultCountry'] ?? $this->defaultCountry;
        $showCountryDropdown = $settings['showCountryDropdown'] ?? 0;

        $addressModel = new AddressModel();

        // This defaults to Sprout Base and we need it to get updated to look
        // in the Sprout Forms Form Template location like other fields.
        SproutBaseFields::$app->addressHelper->setBaseAddressFieldPath('');

        SproutBaseFields::$app->addressHelper->setNamespace($name);

        if (isset($this->highlightCountries) && count($this->highlightCountries)) {
            SproutBaseFields::$app->addressHelper->setHighlightCountries($this->highlightCountries);
        }

        SproutBaseFields::$app->addressHelper->setCountryCode($countryCode);
        SproutBaseFields::$app->addressHelper->setAddressModel($addressModel);
        SproutBaseFields::$app->addressHelper->setLanguage($this->defaultLanguage);

        if (count($this->highlightCountries)) {
            SproutBaseFields::$app->addressHelper->setHighlightCountries($this->highlightCountries);
        }

        $countryInputHtml = SproutBaseFields::$app->addressHelper->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = SproutBaseFields::$app->addressHelper->getAddressFormHtml();

        $rendered = Craft::$app->getView()->renderTemplate(
            'address/input', [
                'field' => $this,
                'name' => $this->handle,
                'renderingOptions' => $renderingOptions,
                'addressFormHtml' => TemplateHelper::raw($addressFormHtml),
                'countryInputHtml' => TemplateHelper::raw($countryInputHtml),
                'showCountryDropdown' => $showCountryDropdown
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @return array
     */
    public function getElementValidationRules(): array
    {
        return ['validateAddress'];
    }

    /**
     * @param Element|ElementInterface $element
     *
     * @return bool
     */
    public function validateAddress(ElementInterface $element): bool
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

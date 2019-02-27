<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\Json;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;


use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutbasefields\models\Phone as PhoneModel;

/**
 *
 * @property array  $elementValidationRules
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property mixed  $exampleInputHtml
 * @property array  $countries
 */
class Phone extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string|null
     */
    public $customPatternErrorMessage;

    /**
     * @var bool|null
     */
    public $limitToSingleCountry;

    /**
     * @var string|null
     */
    public $country;

    /**
     * @var string|null
     */
    public $placeholder;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Phone');
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/phone.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/formfields/phone/settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return array|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        // Submitting an Element to be saved
        if (is_object($value) && get_class($value) == PhoneModel::class) {
            return $value->getAsJson();
        }

        // Save the phone as json with the number and country
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $phoneInfo = [];

        if (is_array($value)) {
            $namespace = $element->getFieldParamNamespace();
            $namespace = $namespace.'.'.$this->handle;
            $phoneInfo = Craft::$app->getRequest()->getBodyParam($namespace);
            // bad phone or empty phone
        }

        if (is_string($value)) {
            $phoneInfo = Json::decode($value);
        }

        if (!isset($phoneInfo['phone'], $phoneInfo['country'])) {
            return null;
        }
        // Always return array
        return new PhoneModel($phoneInfo['phone'], $phoneInfo['country']);
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/phone/example',
            [
                'field' => $this
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
        $countryId = Craft::$app->getView()->formatInputId($name.'-country');
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);
        $namespaceCountryId = Craft::$app->getView()->namespaceInputId($countryId);
        $countries = $this->getCountries();

        $country = $value['country'] ?? $this->country;
        $val = $value['phone'] ?? null;

        return Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/phone/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'namespaceCountryId' => $namespaceCountryId,
                'id' => $inputId,
                'countryId' => $countryId,
                'name' => $this->handle,
                'value' => $val,
                'placeholder' => $this->placeholder,
                'countries' => $countries,
                'country' => $country,
                'limitToSingleCountry' => $this->limitToSingleCountry
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
        $country = $value['country'] ?? $this->country;
        $val = $value['phone'] ?? null;

        $rendered = Craft::$app->getView()->renderTemplate('phone/input',
            [
                'name' => $name,
                'value' => $val,
                'field' => $this,
                'country' => $country,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if ($value->international) {
            $fullNumber = $value->international;
            $html = '<a href="tel:'.$fullNumber.'" target="_blank">'.$fullNumber.'</a>';
        }

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validatePhone'];
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validatePhone(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if ($this->required && !$value->phone) {
            $element->addError(
                $this->handle,
                Craft::t('sprout-forms', '{field} cannot be blank.', [
                    'field' => $this->name
                ])
            );
        }

        if ($value->country && $value->phone && !SproutBaseFields::$app->phoneField->validate($value->phone, $value->country)) {
            $element->addError(
                $this->handle,
                SproutBaseFields::$app->phoneField->getErrorMessage($this, $value->country)
            );
        }
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        return SproutBaseFields::$app->phoneField->getCountries();
    }
}

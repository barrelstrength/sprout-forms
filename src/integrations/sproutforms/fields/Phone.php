<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;


use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\contracts\BaseFormField;
use barrelstrength\sproutbase\sproutfields\models\Phone as PhoneModel;

class Phone extends BaseFormField implements PreviewableFieldInterface
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
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/phone.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout-forms/_formtemplates/fields/phone/settings',
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

        if (is_array($value)){
            $namespace = $element->getFieldParamNamespace();
            $namespace = $namespace.'.'.$this->handle;
            $phoneInfo = Craft::$app->getRequest()->getBodyParam($namespace);
            // bad phone or empty phone
        }

        if (is_string($value)) {
            $phoneInfo = json_decode($value, true);
        }

        if (!isset($phoneInfo['phone']) || !isset($phoneInfo['country'])){
            return null;
        }
        // Always return array
        $phoneModel = new PhoneModel($phoneInfo['phone'], $phoneInfo['country']);
        return $phoneModel;
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_formtemplates/fields/phone/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
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
            'sprout-base-fields/_fields/phone/input',
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
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
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
     * @param ElementInterface $element
     *
     * @return void
     */
    public function validatePhone(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if ($this->required){
            if (!$value->phone){
                $element->addError(
                    $this->handle,
                    Craft::t('sprout-forms','{field} cannot be blank', [
                        'field' => $this->name
                    ])
                );
            }
        }

        if ($value->country && $value->phone) {
            if (!SproutBase::$app->phone->validate($value->phone, $value->country)) {
                $element->addError(
                    $this->handle,
                    SproutBase::$app->phone->getErrorMessage($this, $value->country)
                );
            }
        }
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        $countries = SproutBase::$app->phone->getCountries();

        return $countries;
    }
}

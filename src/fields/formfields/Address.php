<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbasefields\base\AddressFieldTrait;
use barrelstrength\sproutbasefields\models\Address as AddressModel;
use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutforms\base\FormField;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\errors\SiteNotFoundException;
use craft\helpers\Template as TemplateHelper;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 *
 * @property array       $elementValidationRules
 * @property string      $contentColumnType
 * @property string      $svgIconPath
 * @property array       $compatibleCraftFields
 * @property array       $compatibleCraftFieldTypes
 * @property null|string $settingsHtml
 * @property string      $exampleInputHtml
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
    public static function hasContentColumn(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasMultipleLabels(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/map-marker-alt.svg';
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return SproutBaseFields::$app->addressField->getSettingsHtml($this);
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return SproutBaseFields::$app->addressField->getInputHtml($this, $value, $element);
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return AddressModel|mixed|null
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        return SproutBaseFields::$app->addressField->normalizeValue($this, $value, $element);
    }

    /**
     * @param ElementInterface $element
     * @param bool             $isNew
     *
     * @throws Throwable
     * @throws Exception
     * @throws StaleObjectException
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        SproutBaseFields::$app->addressField->afterElementSave($this, $element, $isNew);
        parent::afterElementSave($element, $isNew);
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        $name = $this->handle;
        $settings = $this->getSettings();

        $countryCode = $settings['defaultCountry'] ?? $this->defaultCountry;
        $showCountryDropdown = $settings['showCountryDropdown'] ?? 0;

        $addressModel = new AddressModel();

        // This defaults to Sprout Base and we need it to get updated to look
        // in the Sprout Forms Form Template location like other fields.
        SproutBaseFields::$app->addressFormatter->setBaseAddressFieldPath('');

        SproutBaseFields::$app->addressFormatter->setNamespace($name);

        if (isset($this->highlightCountries) && count($this->highlightCountries)) {
            SproutBaseFields::$app->addressFormatter->setHighlightCountries($this->highlightCountries);
        }

        SproutBaseFields::$app->addressFormatter->setCountryCode($countryCode);
        SproutBaseFields::$app->addressFormatter->setAddressModel($addressModel);
        SproutBaseFields::$app->addressFormatter->setLanguage($this->defaultLanguage);

        if (count($this->highlightCountries)) {
            SproutBaseFields::$app->addressFormatter->setHighlightCountries($this->highlightCountries);
        }

        $countryInputHtml = SproutBaseFields::$app->addressFormatter->getCountryInputHtml($showCountryDropdown);
        $addressFormHtml = SproutBaseFields::$app->addressFormatter->getAddressFormHtml();

        $rendered = Craft::$app->getView()->renderTemplate('address/input', [
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

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        return [
            'barrelstrength\\sproutfields\\fields\\Address'
        ];
    }
}

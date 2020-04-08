<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutforms\base\FormFieldTrait;
use barrelstrength\sproutforms\fields\formfields\base\BaseOptionsConditionalTrait;
use barrelstrength\sproutforms\rules\conditions\ContainsCondition;
use barrelstrength\sproutforms\rules\conditions\DoesNotContainCondition;
use barrelstrength\sproutforms\rules\conditions\DoesNotEndWithCondition;
use barrelstrength\sproutforms\rules\conditions\DoesNotStartWithCondition;
use barrelstrength\sproutforms\rules\conditions\EndsWithCondition;
use barrelstrength\sproutforms\rules\conditions\IsCondition;
use barrelstrength\sproutforms\rules\conditions\IsNotCondition;
use barrelstrength\sproutforms\rules\conditions\StartsWithCondition;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\fields\data\SingleOptionFieldData;
use craft\fields\Dropdown as CraftDropdownField;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\StringHelper;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\db\Schema;

/**
 *
 * @property array  $elementValidationRules
 * @property string $contentColumnType
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property array  $compatibleConditions
 * @property mixed  $exampleInputHtml
 */
class EmailDropdown extends CraftDropdownField
{
    use FormFieldTrait;
    use BaseOptionsConditionalTrait;

    /**
     * @var string
     */
    public $cssClasses;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Email Dropdown');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/share.svg';
    }

    public function normalizeValue($value, ElementInterface $element = null)
    {
        // Make the unobfuscated values available to email notifications
        if ($value && Craft::$app->request->getIsSiteRequest() && Craft::$app->getRequest()->getIsPost()) {
            // Swap our obfuscated number value (e.g. 1) with the email value
            $selectedOption = $this->options[$value];
            $value = $selectedOption['value'];
        }

        return parent::normalizeValue($value, $element);
    }

    public function serializeValue($value, ElementInterface $element = null)
    {
        if (Craft::$app->getRequest()->isSiteRequest && $value->selected) {
            // Default fist position.
            $pos = $value->value ?: 0;

            if (isset($this->options[$pos])) {
                return $this->options[$pos]['value'];
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml()
    {
        $options = $this->options;

        if (!$options) {
            $options = [['label' => '', 'value' => '']];
        }

        return Craft::$app->getView()->renderTemplateMacro('_includes/forms',
            'editableTableField',
            [
                [
                    'label' => $this->optionsSettingLabel(),
                    'instructions' => Craft::t('sprout-forms', 'Define the available options.'),
                    'id' => 'options',
                    'name' => 'options',
                    'addRowLabel' => Craft::t('sprout-forms', 'Add an option'),
                    'cols' => [
                        'label' => [
                            'heading' => Craft::t('sprout-forms', 'Name'),
                            'type' => 'singleline',
                            'autopopulate' => 'value'
                        ],
                        'value' => [
                            'heading' => Craft::t('sprout-forms', 'Email'),
                            'type' => 'singleline',
                            'class' => 'code'
                        ],
                        'default' => [
                            'heading' => Craft::t('sprout-forms', 'Default?'),
                            'type' => 'checkbox',
                            'class' => 'thin'
                        ],
                    ],
                    'rows' => $options
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        /** @var SingleOptionFieldData $value */
        $valueOptions = $value->getOptions();
        $anySelected = SproutBaseFields::$app->utilities->isAnyOptionsSelected(
            $valueOptions,
            $value->value
        );

        $name = $this->handle;
        $value = $value->value;

        if ($anySelected === false) {
            $value = $this->defaultValue();
        }

        $options = $this->options;

        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/emaildropdown/input',
            [
                'name' => $name,
                'value' => $value,
                'options' => $options
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/emaildropdown/example',
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
     * @throws Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        $selectedValue = $value->value ?? null;

        $options = $this->options;
        $options = SproutBaseFields::$app->emailDropdownField->obfuscateEmailAddresses($options, $selectedValue);

        $rendered = Craft::$app->getView()->renderTemplate('emaildropdown/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'options' => $options,
                'field' => $this
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

        if ($value) {
            $html = $value->label.': <a href="mailto:'.$value.'" target="_blank">'.$value.'</a>';
        }

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validateEmailDropdown'];
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validateEmailDropdown(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle)->value;

        $invalidEmails = [];

        $emailString = $this->options[$value]->value ?? null;

        if ($emailString) {

            $emailAddresses = StringHelper::split($emailString);
            $emailAddresses = array_unique($emailAddresses);

            foreach ($emailAddresses as $emailAddress) {
                if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = Craft::t('sprout-forms', 'Email does not validate: '.$emailAddress);
                }
            }
        }

        if (!empty($invalidEmails)) {
            foreach ($invalidEmails as $invalidEmail) {
                $element->addError($this->handle, $invalidEmail);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftPlainText::class,
            CraftDropdownField::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function getCompatibleConditions()
    {
        return [
            new IsCondition(),
            new IsNotCondition(),
            new ContainsCondition(),
            new DoesNotContainCondition(),
            new StartsWithCondition(),
            new DoesNotStartWithCondition(),
            new EndsWithCondition(),
            new DoesNotEndWithCondition()
        ];
    }
}

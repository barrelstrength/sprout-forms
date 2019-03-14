<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;
use craft\helpers\StringHelper;

/**
 *
 * @property array  $elementValidationRules
 * @property string $contentColumnType
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property mixed  $exampleInputHtml
 */
class EmailDropdown extends BaseOptionsFormField
{
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
     * @return string
     */
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout-forms', 'Dropdown Options');
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/share.svg';
    }

    /**
     * @inheritdoc
     * @throws \yii\base\Exception
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
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
     * @return \Twig_Markup
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup
    {
        $selectedValue = $value->value ?? null;

        $options = $this->options;
        $options = SproutBaseFields::$app->emailDropdownField->obfuscateEmailAddresses($options, $selectedValue);

        $rendered = Craft::$app->getView()->renderTemplate(
            'emaildropdown/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'options' => $options,
                'field' => $this
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    public function normalizeValue($value, ElementInterface $element = null)
    {
        // Make the unobfuscated values available to email notifications
        if (Craft::$app->request->getIsSiteRequest() && Craft::$app->getRequest()->getIsPost()) {
            // Swap our obfuscated number value (e.g. 1) with the email value
            $selectedOption = $this->options[$value];
            $value = $selectedOption['value'];
        }

        return parent::normalizeValue($value, $element);
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
}

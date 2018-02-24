<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;
use craft\helpers\ArrayHelper;

use barrelstrength\sproutbase\SproutBase;

class EmailDropdown extends SproutBaseOptionsField
{
    public static function displayName(): string
    {
        return Craft::t('sprout-forms','Email Dropdown');
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
    protected function optionsSettingLabel(): string
    {
        return Craft::t('sprout-forms','Dropdown Options');
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return 'fa fa-share';
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/emailselect/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
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
                    'instructions' => Craft::t('sprout-forms','Define the available options.'),
                    'id' => 'options',
                    'name' => 'options',
                    'addRowLabel' => Craft::t('sprout-forms','Add an option'),
                    'cols' => [
                        'label' => [
                            'heading' => Craft::t('sprout-forms','Name'),
                            'type' => 'singleline',
                            'autopopulate' => 'value'
                        ],
                        'value' => [
                            'heading' => Craft::t('sprout-forms','Email'),
                            'type' => 'singleline',
                            'class' => 'code'
                        ],
                        'default' => [
                            'heading' => Craft::t('sprout-forms','Default?'),
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
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $valueOptions = $value->getOptions();
        $anySelected = SproutBase::$app->utilities->isAnyOptionsSelected(
            $valueOptions,
            $value->value
        );

        $name = $this->handle;
        $value = $value->value;

        if ($anySelected === false) {
            $value = $this->defaultValue();
        }

        $options = $this->options;

        return Craft::$app->getView()->renderTemplate('sprout-base/sproutfields/_fields/emaildropdown/input',
            [
                'name' => $name,
                'value' => $value,
                'options' => $options
            ]
        );
    }

    /**
     * @param FieldModel $field
     * @param mixed      $value
     * @param array      $settings
     * @param array      $renderingOptions
     *
     * @return \Twig_Markup
     */
    public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $selectedValue = isset($value->value) ? $value->value : null;

        $options = $settings['options'];
        $options = SproutBase::$app->emailDropdown->obfuscateEmailAddresses($options, $selectedValue);

        $rendered = Craft::$app->getView()->renderTemplate(
            'emailselect/input',
            [
                'name' => $field->handle,
                'value' => $value,
                'options' => $options,
                'settings' => $settings,
                'field' => $field
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = 'validateEmailDropdown';

        return $rules;
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     *
     * @param ElementInterface $element
     *
     * @return void
     */
    public function validateEmailDropdown(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle)->value;

        $emailAddresses = ArrayHelper::toArray($value);

        $emailAddresses = array_unique($emailAddresses);

        if (count($emailAddresses)) {
            $invalidEmails = [];
            foreach ($emailAddresses as $emailAddress) {
                if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                    $invalidEmails[] = Craft::t('sprout-forms',
                        $emailAddress." email does not validate"
                    );
                }
            }
        }

        if (!empty($invalidEmails)) {
            foreach ($invalidEmails as $invalidEmail) {
                $element->addError($this->handle, $invalidEmail, $this);
            }
        }
    }

    /**
     * @inheritdoc

    public function normalizeValue($value, ElementInterface $element = null)
    {
        #$value = parent::normalizeValue($value, $element);
        Craft::dd($value);

        if (isset($value->value))
        {
            $val = $value->value;
            $options = $value->getOptions();
            $value->value = $options[$val]->value;
            $value->label = $options[$val]->label;
        }

        return $value;
    } */

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
}

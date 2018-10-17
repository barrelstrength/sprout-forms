<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutforms\base\FormField;
use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;
use craft\base\ElementInterface;

/**
 * Class SproutFormsCheckboxesField
 *
 */
class OptIn extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string
     */
    public $optInMessage;

    /**
     * @var bool
     */
    public $selectedByDefault;

    public function init()
    {
        $this->optInMessage = Craft::t('sprout-forms', 'Agree to terms?');

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Opt-in');
    }

    /**
     * @inheritdoc
     */
    public function displayLabel()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function displayInstructionsField()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/check-square.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/optin/settings',
            [
                'field' => $this,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/optin/input',
            [
                'name' => $this->handle,
                'namespaceInputId' => $namespaceInputId,
                'label' => $this->optInMessage,
                'value' => 1,
                'checked' => $value
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/optin/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'optin/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['optInMessage'], 'required'];

        return $rules;
    }
}

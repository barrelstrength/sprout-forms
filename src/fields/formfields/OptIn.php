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
 *
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property mixed  $exampleInputHtml
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

    /**
     * @var string
     */
    public $optInValueWhenTrue;

    /**
     * @var string
     */
    public $optInValueWhenFalse;

    public function init()
    {
        if ($this->optInMessage === null) {
            $this->optInMessage = Craft::t('sprout-forms', 'Agree to terms?');
        }

        if ($this->optInValueWhenTrue === null) {
            $this->optInValueWhenTrue = Craft::t('sprout-forms', 'Yes');
        }

        if ($this->optInValueWhenFalse === null) {
            $this->optInValueWhenFalse= Craft::t('sprout-forms', 'No');
        }

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
    public function displayLabel(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function displayInstructionsField(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/check-square.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
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
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
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
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/optin/example',
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
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup
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

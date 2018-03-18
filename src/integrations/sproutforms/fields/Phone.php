<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutbase\web\assets\sproutfields\phone\PhoneFieldAsset;

class Phone extends SproutFormsBaseField implements PreviewableFieldInterface
{
    /**
     * @var string|null
     */
    public $customPatternErrorMessage;

    /**
     * @var bool|null
     */
    public $customPatternToggle;

    /**
     * @var bool|null
     */
    public $inputMask;

    /**
     * @var string|null
     */
    public $mask;

    /**
     * @var string|null
     */
    public $placeholder;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Phone');
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
            'sprout-forms/_components/fields/phone/settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/phone/example',
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
//        $view = Craft::$app->getView();
//        $view->registerAssetBundle(PhoneFieldAsset::class);
//        $name = $this->handle;
//        $inputId = Craft::$app->getView()->formatInputId($name);
//        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);
//
//        return Craft::$app->getView()->renderTemplate('sprout-base/sproutfields/_fields/phone/input',
//            [
//                'id' => $namespaceInputId,
//                'name' => $this->handle,
//                'value' => $value,
//                'field' => $this
//            ]
//        );

        return '';
    }

    /**
     * @param mixed                                            $value
     * @param array|null                                       $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFormInputHtml($value, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $name = $this->handle;
        $namespaceInputId = $this->getNamespace().'-'.$name;
//        $mask = $settings['mask'];

//        $mask = preg_quote($settings['mask']);
        // Do no escape "-" html5 does not treat it as special chars
//        $mask = str_replace("\\-", '-', $mask);
//        $pattern = SproutBase::$app->phone->convertMaskToRegEx($mask);
        $mask = '';
        $pattern = '';
//        $pattern = trim($pattern, '/');

//        $errorMessage = SproutBase::$app->phone->getErrorMessage($field);
        $errorMessage = '';

        $rendered = Craft::$app->getView()->renderTemplate('phone/input',
            [
                'name' => $name,
                'value' => $value,
                'field' => $this,
                'pattern' => $pattern,
                'errorMessage' => $errorMessage,
                'namespaceInputId' => $namespaceInputId,
                'renderingOptions' => $renderingOptions
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
        return ['validatePhone'];
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
    public function validatePhone(ElementInterface $element)
    {
//        $value = $element->getFieldValue($this->handle);
//
//        $handle = $this->handle;
//        $name = $this->name;
//
//        if ($this->mask == "") {
//            $this->mask = SproutBase::$app->phone->getDefaultMask();
//        }
//
//        if (!SproutBase::$app->phone->validate($value, $this->mask)) {
//            $element->addError(
//                $this->handle,
//                SproutBase::$app->phone->getErrorMessage($this)
//            );
//        }
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $html = '';

        if ($value) {
            $formatter = Craft::$app->getFormatter();

            $html = '<a href="tel:'.$value.'" target="_blank">'.$value.'</a>';
        }

        return $html;
    }
}

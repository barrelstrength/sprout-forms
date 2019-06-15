<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\fields\PlainText as CraftPlainText;
use craft\fields\Url as CraftUrl;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;

use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutbasefields\web\assets\url\UrlFieldAsset;

/**
 *
 * @property array  $elementValidationRules
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property mixed  $exampleInputHtml
 */
class Url extends FormField implements PreviewableFieldInterface
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
    public $customPatternToggle;

    /**
     * @var string|null
     */
    public $customPattern;

    /**
     * @var string|null
     */
    public $placeholder;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'URL');
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/chain.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/formfields/url/settings',
            [
                'field' => $this,
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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\InvalidConfigException
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(UrlFieldAsset::class);

        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBaseFields::$app->utilities->getFieldContext($this, $element);

        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/url/input', [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'value' => $value,
                'fieldContext' => $fieldContext,
                'placeholder' => $this->placeholder
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/url/example',
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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig\Markup
    {
        $attributes = $this->getAttributes();
        $errorMessage = SproutBaseFields::$app->urlField->getErrorMessage($attributes['name'], $this);
        $placeholder = $this->placeholder ?? '';

        $rendered = Craft::$app->getView()->renderTemplate(
            'url/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'pattern' => $this->customPattern,
                'errorMessage' => $errorMessage,
                'renderingOptions' => $renderingOptions,
                'placeholder' => $placeholder
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        if ($value) {
            return '<a href="'.$value.'" target="_blank">'.$value.'</a>';
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validateUrl'];
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     * @param ElementInterface $element
     *
     * @return void
     */
    public function validateUrl(ElementInterface $element)
    {
        /** @var Element $element */
        $value = $element->getFieldValue($this->handle);

        if (!SproutBaseFields::$app->urlField->validate($value, $this)) {
            $element->addError(
                $this->handle,
                SproutBaseFields::$app->urlField->getErrorMessage($this->name, $this)
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        return [
            CraftPlainText::class,
            CraftUrl::class,
            'barrelstrength\\sproutfields\\fields\\Url'
        ];
    }
}

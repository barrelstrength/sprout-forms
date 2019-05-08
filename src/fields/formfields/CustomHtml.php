<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;

use barrelstrength\sproutforms\base\FormField;

/**
 *
 * @property string $contentColumnType
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property array  $compatibleCraftFields
 * @property mixed  $exampleInputHtml
 */
class CustomHtml extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var bool
     */
    public $allowRequired = false;

    /**
     * @var string
     */
    public $customHtml;

    /**
     * @var bool
     */
    public $hideLabel;

    /**
     * @inheritdoc
     */
    public function isPlainInput(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function displayInstructionsField(): bool
    {
        return false;
    }

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Custom HTML');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/code.svg';
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/customhtml/settings',
            [
                'field' => $this
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
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/customhtml/input',
            [
                'id' => $this->handle,
                'name' => $this->handle,
                'value' => $value,
                'field' => $this
            ]);
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/customhtml/example',
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
        $rendered = Craft::$app->getView()->renderTemplate(
            'customhtml/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }
}

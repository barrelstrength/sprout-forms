<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\services\Forms;
use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\errors\MissingComponentException;
use craft\fields\Dropdown as CraftDropdown;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\Template as TemplateHelper;
use Exception;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;

/**
 *
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property mixed  $exampleInputHtml
 */
class Invisible extends FormField implements PreviewableFieldInterface
{
    /**
     * @var bool
     */
    public $allowRequired = false;

    /**
     * @var bool
     */
    public $allowEdits;

    /**
     * @var bool
     */
    public $hideValue;

    /**
     * @var string|null
     */
    public $value;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Invisible');
    }

    /**
     * @return bool
     */
    public function isPlainInput(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/eye-slash.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/invisible/settings',
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/invisible/input',
            [
                'id' => $namespaceInputId,
                'name' => $name,
                'value' => $value,
                'field' => $this
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
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/invisible/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @param            $value
     * @param array|null $renderingOptions
     *
     * @return string
     * @throws Throwable
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        $this->preProcessInvisibleValue();

        $html = '<input type="hidden" name="'.$this->handle.'">';

        return TemplateHelper::raw($html);
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return mixed
     * @throws MissingComponentException
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            $invisibleValue = Craft::$app->getSession()->get($this->handle);

            // If we have have a value stored in the session for the Invisible Field, use it
            if ($invisibleValue) {
                $value = $invisibleValue;
            }

            // Clean up so the session value doesn't persist
            Craft::$app->getSession()->set($this->handle, null);
        }

        return parent::normalizeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        $hiddenValue = '';

        if ($value !== '' && $value !== null) {
            $hiddenValue = $this->hideValue ? '&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;' : $value;
        }

        return $hiddenValue;
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftPlainText::class,
            CraftDropdown::class
        ];
    }

    /**
     * @return string
     * @throws Throwable
     */
    private function preProcessInvisibleValue(): string
    {
        $value = '';

        if ($this->value) {
            try {
                $value = Craft::$app->view->renderObjectTemplate($this->value, Forms::getFieldVariables());
                Craft::$app->getSession()->set($this->handle, $value);
            } catch (Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return $value;
    }
}

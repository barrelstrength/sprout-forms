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
class Hidden extends FormField implements PreviewableFieldInterface
{
    /**
     * @var bool
     */
    public $allowRequired = false;

    /**
     * @var bool
     */
    public $allowEdits = false;

    /**
     * @var string|null The maximum allowed number
     */
    public $value = '';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Hidden');
    }

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
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/user-secret.svg';
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/hidden/settings',
            [
                'field' => $this,
            ]);
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
        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/hidden/input',
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/hidden/example',
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
     * @throws Throwable
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        if ($this->value) {
            try {
                $value = Craft::$app->view->renderObjectTemplate($this->value, Forms::getFieldVariables());
            } catch (Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        $rendered = Craft::$app->getView()->renderTemplate('hidden/input',
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
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftPlainText::class,
            CraftDropdown::class
        ];
    }
}

<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields;

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
use craft\fields\Dropdown as CraftDropdownField;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

/**
 * Class SproutFormsDropdownField
 *
 * @property string $modelName
 * @property string $svgIconPath
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property array  $compatibleConditions
 * @property mixed  $exampleInputHtml
 */
class Dropdown extends CraftDropdownField
{
    use FormFieldTrait;
    use BaseOptionsConditionalTrait;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return CraftDropdownField::class;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/chevron-circle-down.svg';
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/dropdown/example',
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
        $rendered = Craft::$app->getView()->renderTemplate('dropdown/input',
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
            CraftDropdownField::class
        ];
    }

    /**
     * @inheritdoc
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

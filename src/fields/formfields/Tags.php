<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutforms\base\FormFieldTrait;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\fields\Tags as CraftTags;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

/**
 * @property string $svgIconPath
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property mixed  $exampleInputHtml
 */
class Tags extends CraftTags
{
    use FormFieldTrait;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string Template to use for settings rendering
     */
    protected $settingsTemplate = 'sprout-forms/_components/fields/formfields/elementfieldsettings';

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/tags.svg';
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/tags/example',
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
        $tags = SproutForms::$app->frontEndFields->getFrontEndTags($this->getSettings());

        $rendered = Craft::$app->getView()->renderTemplate('tags/input',
            [
                'name' => $this->handle,
                'value' => $value->ids(),
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'tags' => $tags,
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
            CraftTags::class
        ];
    }
}

<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\elements\User;
use craft\fields\Entries as CraftEntries;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutforms\SproutForms;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;

/**
 * Class SproutFormsEntriesField
 *
 *
 * @property string $svgIconPath
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property mixed  $exampleInputHtml
 */
class Users extends BaseRelationFormField
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string
     */
    public $usernameFormat = 'fullName';

    protected $settingsTemplate = 'sprout-forms/_components/fields/formfields/users/settings';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Users');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return User::class;
    }


    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/newspaper-o.svg';
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add an User');
    }

    /**
     * @inheritdoc
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/users/example',
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
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        $users = SproutForms::$app->frontEndFields->getFrontEndUsers($this->getSettings());

        $rendered = Craft::$app->getView()->renderTemplate(
            'users/input',
            [
                'name' => $this->handle,
                'value' => $value->ids(),
                'field' => $this,
                'renderingOptions' => $renderingOptions,
                'users' => $users,
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
            CraftEntries::class
        ];
    }
}

<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutforms\base\FormFieldTrait;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\fields\Assets as CraftAssets;
use craft\helpers\Template as TemplateHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;

/**
 * @property array      $elementValidationRules
 * @property array      $fileKindOptions
 * @property string     $svgIconPath
 * @property array      $sourceOptions
 * @property array      $compatibleCraftFields
 * @property array      $compatibleCraftFieldTypes
 * @property array      $contentGqlType
 * @property bool|array $eagerLoadingGqlConditions
 * @property mixed      $settingsHtml
 * @property mixed      $exampleInputHtml
 */
class FileUpload extends CraftAssets
{
    use FormFieldTrait;

    /**
     * @var string
     */
    public $cssClasses;

    /**
     * Override the CP default for front-end use.
     *
     * @inheritDoc
     */
    public $useSingleFolder = true;

    /**
     * @var string Template to use for settings rendering
     */
    protected $settingsTemplate = 'sprout-forms/_components/fields/formfields/fileupload/settings';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        
        if (!$this->singleUploadLocationSubpath) {
            $settings = SproutForms::$app->getSettings();
            $this->singleUploadLocationSubpath = $settings->defaultUploadLocationSubpath;
        }
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'File Upload');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('sprout-forms', 'Add a file');
    }

    /**
     * Make these attributes available as Form Field settings
     *
     * @return array
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'allowedKinds';
        $attributes[] = 'defaultUploadLocationSource';
        $attributes[] = 'defaultUploadLocationSubpath';
        $attributes[] = 'singleUploadLocationSource';
        $attributes[] = 'singleUploadLocationSubpath';
        $attributes[] = 'restrictFiles';
        $attributes[] = 'allowedKinds';

        return $attributes;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/cloud-upload.svg';
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/fileupload/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param Entry      $entry
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getFrontEndInputHtml($value, Entry $entry, array $renderingOptions = null): Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate('fileupload/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'entry' => $entry,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    protected function settingsTemplateVariables(): array
    {
        $variables = parent::settingsTemplateVariables();

        $settings = SproutForms::$app->getSettings();

        $allowedSourceOptions = $this->getSourceOptions();

        if ($settings->allowedAssetVolumes !== '*') {
            foreach ($allowedSourceOptions as $key => $sourceOption) {
                if (!in_array($sourceOption['value'], $settings->allowedAssetVolumes, false)) {
                    unset($allowedSourceOptions[$key]);
                }
            }
        }

        $variables['allowedSourceOptions'] = $allowedSourceOptions;

        return $variables;
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftAssets::class
        ];
    }
}

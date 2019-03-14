<?php

namespace barrelstrength\sproutforms\fields\formfields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Db;
use craft\helpers\Localization;
use craft\i18n\Locale;

use barrelstrength\sproutforms\base\FormField;

/**
 * Class SproutFormsNumberField
 *
 *
 * @property string      $contentColumnType
 * @property string      $svgIconPath
 * @property null|string $settingsHtml
 * @property mixed       $exampleInputHtml
 */
class Number extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var int|float The minimum allowed number
     */
    public $min = 0;

    /**
     * @var int|float|null The maximum allowed number
     */
    public $max;

    /**
     * @var int The number of digits allowed after the decimal point
     */
    public $decimals = 0;

    /**
     * @var int|null The size of the field
     */
    public $size;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Normalize $max
        if ($this->max !== null && empty($this->max)) {
            $this->max = null;
        }

        // Normalize $min
        if ($this->min !== null && empty($this->min)) {
            $this->min = null;
        }

        // Normalize $decimals
        if (!$this->decimals) {
            $this->decimals = 0;
        }

        // Normalize $size
        if ($this->size !== null && $this->size === null) {
            $this->size = null;
        }
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Number');
    }


    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/hashtag.svg';
    }

    /**
     * @inheritdoc
     * @throws \yii\base\Exception
     */
    public function getContentColumnType(): string
    {
        return Db::getNumericalColumnType($this->min, $this->max, $this->decimals);
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        // Is this a post request?
        $request = Craft::$app->getRequest();

        if (!$request->getIsConsoleRequest() && $request->getIsPost() && $value !== '') {
            // Normalize the number and make it look like this is what was posted
            $value = Localization::normalizeNumber($value);
        }

        return $value;
    }

    /**
     * @return null|string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/formfields/number/settings',
            [
                'field' => $this,
            ]
        );

        return $rendered;
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $decimals = $this->decimals;

        // If decimals is 0 (or null, empty for whatever reason), don't run this
        if ($decimals) {
            $decimalSeparator = Craft::$app->getLocale()->getNumberSymbol(Locale::SYMBOL_DECIMAL_SEPARATOR);
            $value = number_format($value, $decimals, $decimalSeparator, '');
        }

        return Craft::$app->getView()->renderTemplate('_includes/forms/text', [
            'name' => $this->handle,
            'value' => $value,
            'size' => $this->size
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/number/example',
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
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): \Twig_Markup
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'number/input',
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
        $rules[] = [['min', 'max'], 'number'];
        $rules[] = [['decimals', 'size'], 'integer'];
        $rules[] = [
            ['max'],
            'compare',
            'compareAttribute' => 'min',
            'operator' => '>='
        ];

        if (!$this->decimals) {
            $rules[] = [['min', 'max'], 'integer'];
        }

        return $rules;
    }
}

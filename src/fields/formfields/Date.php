<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutforms\base\FormField;
use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\fields\Date as CraftDate;
use craft\fields\PlainText as CraftPlainText;
use craft\helpers\DateTimeHelper;
use craft\helpers\Template as TemplateHelper;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\db\Schema;

/**
 * @property array  $elementValidationRules
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property array  $compatibleCraftFields
 * @property array  $compatibleCraftFieldTypes
 * @property string $contentColumnType
 * @property mixed  $exampleInputHtml
 */
class Date extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var bool
     */
    public $showDate = true;

    /**
     * @var bool
     */
    public $showTime = false;

    /**
     * @var int
     */
    public $minuteIncrement = 30;

    /**
     * @var string YYYY-MM-DD
     */
    public $minimumDate;

    /**
     * @var string YYYY-MM-DD
     */
    public $maximumDate;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        // dateTime => showDate + showTime
        if (isset($config['dateTime'])) {
            switch ($config['dateTime']) {
                case 'showBoth':
                    $config['showDate'] = true;
                    $config['showTime'] = true;
                    break;
                case 'showDate':
                    $config['showDate'] = true;
                    $config['showTime'] = false;
                    break;
                case 'showTime':
                    $config['showDate'] = false;
                    $config['showTime'] = true;
                    break;
            }

            unset($config['dateTime']);
        }

        parent::__construct($config);
    }

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Date/Time');
    }

    public function getContentColumnType(): string
    {
        return Schema::TYPE_DATETIME;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // In case nothing is selected, default to the date.
        if (!$this->showDate && !$this->showTime) {
            $this->showDate = true;
        }
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return DateTime|false|mixed|null
     * @throws Exception
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value && ($date = DateTimeHelper::toDateTime($value)) !== false) {
            return $date;
        }

        return null;
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/date/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/calendar.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        $dateTimeValue = null;

        // If they are both selected or nothing is selected, the select showBoth.
        if ($this->showDate && $this->showTime) {
            $dateTimeValue = 'showBoth';
        } else if ($this->showDate) {
            $dateTimeValue = 'showDate';
        } else if ($this->showTime) {
            $dateTimeValue = 'showTime';
        }

        $options = [15, 30, 60];
        $options = array_combine($options, $options);

        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/date/settings',
            [
                'options' => [
                    [
                        'label' => Craft::t('app', 'Show date'),
                        'value' => 'showDate',
                    ],
                    [
                        'label' => Craft::t('app', 'Show time'),
                        'value' => 'showTime',
                    ],
                    [
                        'label' => Craft::t('app', 'Show date and time'),
                        'value' => 'showBoth',
                    ]
                ],
                'value' => $dateTimeValue,
                'incrementOptions' => $options,
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
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        if ($this->minimumDate) {
            $this->minimumDate = Craft::$app->getView()->renderString($this->minimumDate);
        }

        if ($this->maximumDate) {
            $this->maximumDate = Craft::$app->getView()->renderString($this->maximumDate);
        }

        $rendered = Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/date/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'timeOptions' => $this->getTimeIncrementsAsOptions($this->minuteIncrement)
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @param mixed      $value
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): Markup
    {
        if ($this->minimumDate) {
            $this->minimumDate = Craft::$app->getView()->renderString($this->minimumDate);
        }

        if ($this->maximumDate) {
            $this->maximumDate = Craft::$app->getView()->renderString($this->maximumDate);
        }

        $rendered = Craft::$app->getView()->renderTemplate('date/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'timeOptions' => $this->getTimeIncrementsAsOptions($this->minuteIncrement),
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * Prepare the time dropdown in increments of the selected minuteIncrement.
     *
     * @param int    $minuteIncrement
     * @param string $format
     * @param int    $lower
     * @param int    $upper
     *
     * @return array
     * @throws Exception
     */
    public function getTimeIncrementsAsOptions($minuteIncrement = 30, $format = '', $lower = 0, $upper = 86400): array
    {
        $times = [];

        // Convert minute increment to seconds, 3600 seconds in a minute
        $step = 3600 * ($minuteIncrement / 60);

        if (empty($format)) {
            $format = 'g:i A';
        }

        $i = 0;
        foreach (range($lower, $upper, $step) as $increment) {
            $increment = gmdate('H:i', $increment);

            list($hour, $minutes) = explode(':', $increment);

            $date = new DateTime($hour.':'.$minutes);

            $times[$i]['label'] = $date->format($format);
            $times[$i]['value'] = (string)$increment;

            $i++;
        }

        return $times;
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftPlainText::class,
            CraftDate::class
        ];
    }
}

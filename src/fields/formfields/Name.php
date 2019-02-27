<?php

namespace barrelstrength\sproutforms\fields\formfields;

use barrelstrength\sproutbasefields\SproutBaseFields;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;

use craft\helpers\Json;
use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutbasefields\models\Name as NameModel;
use barrelstrength\sproutforms\base\FormField;

/**
 *
 * @property array  $elementValidationRules
 * @property string $svgIconPath
 * @property mixed  $settingsHtml
 * @property mixed  $exampleInputHtml
 */
class Name extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var bool
     */
    public $displayMultipleFields;

    /**
     * @var bool
     */
    public $displayMiddleName;

    /**
     * @var bool
     */
    public $displayPrefix;

    /**
     * @var bool
     */
    public $displaySuffix;

    /**
     * @var string
     */
    private $hasMultipleLabels = false;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Name');
    }

    /**
     * @inheritdoc
     */
    public function hasMultipleLabels(): bool
    {
        return $this->hasMultipleLabels;
    }

    /**
     * @return string
     */
    public function getSvgIconPath(): string
    {
        return '@sproutbaseicons/user.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_components/fields/formfields/name/settings',
            [
                'field' => $this,
            ]);
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBaseFields::$app->utilities->getFieldContext($this, $element);

        // Set this to false for Quick Entry Dashboard Widget
        // @todo - can we update the Quick Entry widget to expect null?
        $elementId = $element->id ?? false;

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_components/fields/formfields/name/input',
            [
                'namespaceInputId' => $namespaceInputId,
                'id' => $inputId,
                'name' => $name,
                'field' => $this,
                'value' => $value,
                'elementId' => $elementId,
                'fieldContext' => $fieldContext
            ]);

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validateName'];
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     * @param Element|ElementInterface $element
     *
     * @return void
     */
    public function validateName(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if ($this->required && !$value->getFullName()) {
            $element->addError(
                $this->handle,
                Craft::t('sprout-forms', '{field} cannot be blank', [
                    'field' => $this->name
                ])
            );
        }
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/formfields/name/example',
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
        if ($this->displayMultipleFields) {
            $this->hasMultipleLabels = true;
        }

        $rendered = Craft::$app->getView()->renderTemplate(
            'name/input',
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
     * Prepare our Name for use as an NameModel
     *
     * @todo - move to helper as we can use this on both Sprout Forms and Sprout Fields
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return NameModel|mixed
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $nameModel = new NameModel();

        // String value when retrieved from db
        if (is_string($value)) {
            $nameArray = Json::decode($value);
            $nameModel->setAttributes($nameArray, false);
        }

        // Array value from post data
        if (is_array($value) && isset($value['address'])) {

            $nameModel->setAttributes($value['address'], false);

            if ($fullNameShort = $value['address']['fullNameShort'] ?? null) {
                $nameArray = explode(' ', trim($fullNameShort));

                $nameModel->firstName = $nameArray[0] ?? $fullNameShort;
                unset($nameArray[0]);

                $nameModel->lastName = implode(' ', $nameArray);
            }
        }

        return $nameModel;
    }

    /**
     *
     * Prepare the field value for the database.
     *
     * @todo - move to helper as we can use this on both Sprout Forms and Sprout Fields
     *
     * We store the Name as JSON in the content column.
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|bool|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if (empty($value)) {
            return false;
        }

        // Submitting an Element to be saved
        if (is_object($value) && get_class($value) == NameModel::class) {
            return Json::encode($value->getAttributes());
        }

        return $value;
    }
}

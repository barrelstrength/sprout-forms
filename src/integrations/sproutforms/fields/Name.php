<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;

use craft\helpers\Template as TemplateHelper;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\fields\models\Name as NameModel;
use barrelstrength\sproutforms\contracts\BaseFormField;

class Name extends BaseFormField implements PreviewableFieldInterface
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
    public function hasMultipleLabels()
    {
        return $this->hasMultipleLabels;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/user.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_fields/name/settings',
            [
                'field' => $this,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {

        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBase::$app->utilities->getFieldContext($this, $element);

        // Set this to false for Quick Entry Dashboard Widget
        $elementId = ($element != null) ? $element->id : false;

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-base-fields/_fields/name/input',
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
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_formtemplates/fields/name/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed      $value
     * @param array|null $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
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
            $nameArray = json_decode($value, true);
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
            return json_encode($value->getAttributes());
        }

        return $value;
    }
}

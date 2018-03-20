<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\contracts\BaseFormField;
use barrelstrength\sproutbase\web\assets\sproutfields\regularexpression\RegularExpressionFieldAsset;

class RegularExpression extends BaseFormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $customPatternErrorMessage;

    /**
     * @var string
     */
    public $customPattern;

    /**
     * @var string
     */
    public $placeholder;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Regex');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/puzzle-piece.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'sprout-forms/_components/fields/regularexpression/settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(RegularExpressionFieldAsset::class);

        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        $fieldContext = SproutBase::$app->utilities->getFieldContext($this, $element);

        return Craft::$app->getView()->renderTemplate(
            'sprout-base/sproutfields/_fields/regularexpression/input',
            [
                'id' => $namespaceInputId,
                'field' => $this,
                'name' => $name,
                'value' => $value,
                'fieldContext' => $fieldContext,
                'placeholder' => $this->placeholder
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/regularexpression/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @param mixed                                            $value
     * @param array|null                                       $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $placeholder = $this->placeholder ?? '';

        $pattern = $this->customPattern;

        // Do no escape "-" html5 does not treat it as special chars
        $pattern = str_replace("\\-", '-', $pattern);

        $rendered = Craft::$app->getView()->renderTemplate(
            'regularexpression/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'pattern' => $pattern,
                'errorMessage' => $this->customPatternErrorMessage,
                'renderingOptions' => $renderingOptions,
                'placeholder' => $placeholder
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml($value, ElementInterface $element): string
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return ['validateRegularExpression'];
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     * @param ElementInterface $element
     *
     * @return void
     */
    public function validateRegularExpression(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if (!SproutBase::$app->regularExpression->validate($value, $this)) {
            $element->addError($this->handle,
                SproutBase::$app->regularExpression->getErrorMessage($this)
            );
        }
    }
}

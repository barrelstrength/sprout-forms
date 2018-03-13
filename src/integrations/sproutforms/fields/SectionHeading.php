<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutbase\web\assets\sproutfields\notes\QuillAsset;

class SectionHeading extends SproutFormsBaseField
{
    /**
     * @var string
     */
    public $notes;

    /**
     * @var bool
     */
    public $hideLabel;

    /**
     * @var string
     */
    public $output;

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Section Heading');
    }

    /**
     * Define database column
     *
     * @return false
     */
    public function defineContentAttribute()
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function displayInstructionsField()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/header.svg';
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/sectionheading/example',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $reflect = new \ReflectionClass($this);
        $name = $reflect->getShortName();

        $inputId = Craft::$app->getView()->formatInputId($name);
        $view = Craft::$app->getView();
        $namespaceInputId = $view->namespaceInputId($inputId);

        $view->registerAssetBundle(QuillAsset::class);

        $options = [
            'richText' => 'Rich Text',
            'markdown' => 'Markdown',
            'html' => 'HTML'
        ];

        return $view->renderTemplate(
            'sprout-forms/_components/fields/sectionheading/settings',
            [
                'id' => $namespaceInputId,
                'name' => $name,
                'field' => $this,
                'outputOptions' => $options
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);

        if ($this->notes === null) {
            $this->notes = '';
        }

        return Craft::$app->getView()->renderTemplate(
            'sprout-base/sproutfields/_fields/sectionheading/input',
            [
                'id' => $namespaceInputId,
                'name' => $name,
                'field' => $this
            ]
        );
    }

    /**
     * @param \barrelstrength\sproutforms\contracts\FieldModel $field
     * @param mixed                                            $value
     * @param mixed                                            $settings
     * @param array|null                                       $renderingOptions
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFormInputHtml($field, $value, $settings, array $renderingOptions = null): string
    {
        $this->beginRendering();

        $name = $field->handle;
        $namespaceInputId = $this->getNamespace().'-'.$name;

        if ($this->notes === null) {
            $this->notes = '';
        }

        $rendered = Craft::$app->getView()->renderTemplate('sectionheading/input',
            [
                'id' => $namespaceInputId,
                'field' => $this
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }
}

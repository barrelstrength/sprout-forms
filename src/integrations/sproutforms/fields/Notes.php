<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use yii\db\Schema;

use barrelstrength\sproutforms\contracts\SproutFormsBaseField;
use barrelstrength\sproutbase\web\assets\sproutfields\notes\QuillAsset;

class Notes extends SproutFormsBaseField
{
    /**
     * @var string
     */
    public $notes;

    /**
     * @var string
     */
    public $style;

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
        return Craft::t('sprout-forms', 'Notes');
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
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/sticky-note.svg';
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/notes/example',
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
        $name = static::displayName();

        $inputId = Craft::$app->getView()->formatInputId($name);
        $view = Craft::$app->getView();
        $namespaceInputId = $view->namespaceInputId($inputId);

        $view->registerAssetBundle(QuillAsset::class);

        return $view->renderTemplate(
            'sprout-forms/_components/fields/notes/settings',
            [
                'options' => $this->getOptions(),
                'id' => $namespaceInputId,
                'name' => $name,
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = static::displayName();
        $inputId = Craft::$app->getView()->formatInputId($name);
        $namespaceInputId = Craft::$app->getView()->namespaceInputId($inputId);
        // @todo - what to do with the styles?
        $selectedStyleCss = "";

        if (is_null($this->notes)) {
            $this->notes = '';
        }

        return Craft::$app->getView()->renderTemplate(
            'sprout-base/sproutfields/_fields/notes/input',
            [
                'id' => $namespaceInputId,
                'name' => $name,
                'field' => $this,
                'selectedStyleCss' => $selectedStyleCss
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

        $selectedStyle = $settings['style'];
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-forms')->getSettings()->getAttributes();

        $selectedStyleCss = "";

        if (isset($pluginSettings[$selectedStyle])) {
            $selectedStyleCss = str_replace('{{ name }}', $name, $pluginSettings[$selectedStyle]);
        }

        $rendered = Craft::$app->getView()->renderTemplate('notes/input',
            [
                'id' => $namespaceInputId,
                'settings' => $settings,
                'selectedStyleCss' => $selectedStyleCss
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }

    /**
     * @todo - Remove?
     * @return array
     */
    private function getOptions()
    {
        $options = [
            'style' => [
                'default' => 'Default',
                'infoPrimaryDocumentation' => 'Primary Information',
                'infoSecondaryDocumentation' => 'Secondary Information',
                'warningDocumentation' => 'Warning',
                'dangerDocumentation' => 'Danger',
                'highlightDocumentation' => 'Highlight'
            ],
            'output' => [
                'richText' => 'Rich Text',
                'markdown' => 'Markdown',
                'html' => 'HTML'
            ]
        ];

        return $options;
    }
}

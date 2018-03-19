<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;
use yii\db\Schema;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

class CustomHtml extends SproutFormsBaseField implements PreviewableFieldInterface
{
    /**
     * @var bool
     */
    public $allowRequired = false;
    
    /**
     * @var string
     */
    public $customHtml;

    /**
     * @var bool
     */
    public $hideLabel;

    /**
     * @inheritdoc
     */
    public function isPlainInput()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function displayInstructionsField()
    {
        return false;
    }

    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Custom HTML');
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/code.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/customhtml/settings',
            [
                'field' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base/sproutfields/_fields/customhtml/input',
            [
                'id' => $this->handle,
                'name' => $this->handle,
                'value' => $value,
                'field' => $this
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/fields/customhtml/example',
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

        $rendered = Craft::$app->getView()->renderTemplate(
            'customhtml/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        $this->endRendering();

        return TemplateHelper::raw($rendered);
    }
}

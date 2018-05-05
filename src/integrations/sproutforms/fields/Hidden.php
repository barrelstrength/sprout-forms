<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\fields;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\Template as TemplateHelper;
use craft\base\PreviewableFieldInterface;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\contracts\BaseFormField;

class Hidden extends BaseFormField implements PreviewableFieldInterface
{
    /**
     * @var bool
     */
    public $allowRequired = false;

    /**
     * @var bool
     */
    public $allowEdits = false;

    /**
     * @var string|null The maximum allowed number
     */
    public $value = '';

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Hidden');
    }

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
    public function getSvgIconPath()
    {
        return '@sproutbaseicons/user-secret.svg';
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_formtemplates/fields/hidden/settings',
            [
                'field' => $this,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-fields/_fields/hidden/input',
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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_formtemplates/fields/hidden/example',
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
        if ($this->value) {
            try {
                $value = Craft::$app->view->renderObjectTemplate($this->value, parent::getFieldVariables());
            } catch (\Exception $e) {
                SproutForms::error($e->getMessage());
            }
        }

        $rendered = Craft::$app->getView()->renderTemplate(
            'hidden/input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }
}

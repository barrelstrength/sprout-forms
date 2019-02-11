<?php

namespace barrelstrength\sproutforms\validators;

use barrelstrength\sproutforms\SproutForms;
use yii\validators\Validator;
use Craft;

class TemplateOverridesValidator extends Validator
{
    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $settings = $plugin->getSettings();

        if ($settings->enablePerFormTemplateFolderOverride && !$value) {
            $this->addError($object, $attribute, Craft::t('sprout-forms', 'Cannot be blank.'));
        }
    }
}

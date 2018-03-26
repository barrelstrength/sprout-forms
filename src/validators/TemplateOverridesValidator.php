<?php

namespace barrelstrength\sproutforms\validators;

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
        $settings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();
        if ($settings->enablePerFormTemplateFolderOverride) {
            if (!$value) {
                $this->addError($object, $attribute, Craft::t('sprout-forms', "Cannot be blank."));
            }
        }
    }
}

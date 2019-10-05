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

        if (!$value) {
            $this->addError($object, $attribute, Craft::t('sprout-forms', 'Form Template cannot be blank.'));
        }
    }
}

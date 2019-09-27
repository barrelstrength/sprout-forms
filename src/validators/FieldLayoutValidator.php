<?php

namespace barrelstrength\sproutforms\validators;

use barrelstrength\sproutforms\elements\Form;
use craft\models\FieldLayout;
use yii\base\InvalidConfigException;
use yii\validators\Validator;
use Craft;

class FieldLayoutValidator extends Validator
{
    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function validateAttribute($object, $attribute)
    {
        /** @var Form $object */
        $isNew = !$object->id;

        if ($isNew) {
            return;
        }

        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $object->getFieldLayout();

        if (count($fieldLayout->getFields()) === 0) {
            $this->addError($object, $attribute, Craft::t('sprout-forms', 'At least one field required.'));
        }
    }
}

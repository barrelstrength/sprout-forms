<?php

namespace barrelstrength\sproutforms\validators;

use yii\validators\Validator;

class EnabledNotificationValidator extends Validator
{
    public $skipOnEmpty = false;

    /**
     * If Notifications are enabled, make sure all Notification fields are set
     *
     * @todo - update to provide specific validation for email fields and allow
     *
     * @param \yii\base\Model $object
     * @param string          $attribute
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        if ($object->notificationEnabled && ($value == "")) {
            $this->addError($object, $attribute, Craft::t('sprout-forms', 'All notification fields are required when notifications are enabled.'));
        }
    }
}

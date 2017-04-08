<?php
namespace barrelstrength\sproutforms\validators;

use yii\validators\Validator;
use barrelstrength\sproutforms\SproutForms;

class EnabledNotificationValidator extends Validator
{
	public $skipOnEmpty = false;
	/**
	 * If Notifications are enabled, make sure all Notification fields are set
	 * @todo - update to provide specific validation for email fields and allow
	 * {objectSyntax}
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;

		if ($object->notificationEnabled && ($value == ""))
		{
			$this->addError($object, $attribute, SproutForms::t('All notification fields are required when notifications are enabled.'));
		}
	}
}

<?php
namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use craft\web\Controller as BaseController;

class NotificationsController extends BaseController
{
	public function actionIndex()
	{
		$notifications = NotificationEmail::find()
			->where(['eventId' => 'barrelstrength\sproutforms\integrations\sproutemail\events\SaveEntryEvent'])
			->andWhere(['elements.enabled' => [1, 0]])
			->all();

		return $this->renderTemplate('sprout-base/sproutemail/notifications/index', [
			'notifications' => $notifications
		]);
	}
}

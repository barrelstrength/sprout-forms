<?php
namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutbase\elements\sproutemail\NotificationEmail;
use Craft;
use craft\web\Controller as BaseController;

class NotificationsController extends BaseController
{
	public function actionIndex()
	{
		$notifications = NotificationEmail::find()
			->where(['eventId' => 'barrelstrength\sproutemail\integrations\sproutemail\events\UsersSave'])
			->all();

		return $this->renderTemplate('sprout-base/sproutemail/notifications/index', [
			'notifications' => $notifications
		]);
	}
}

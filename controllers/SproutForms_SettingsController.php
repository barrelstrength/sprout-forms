<?php
namespace Craft;

class SproutForms_SettingsController extends BaseController
{
	/**
	 * Save Settings to the Database
	 *
	 * @return mixed Return to Page
	 */
	public function actionSettingsIndexTemplate()
	{
		$settingsTemplate = craft()->request->getSegment(3);

		$results = craft()->db->createCommand()
			->select('settings')
			->from('plugins')
			->where('class=:class', array(':class' => 'SproutForms'))
			->queryScalar();

		$results = JsonHelper::decode($results);

		$settings = SproutForms_SettingsModel::populateModel($results);

		$this->renderTemplate('sproutforms/settings/' . $settingsTemplate, array(
			'settings' => $settings
		));
	}

	/**
	 * Save Plugin Settings
	 *
	 * @return void
	 */
	public function actionSaveSettings()
	{
		$this->requirePostRequest();
		$settings = craft()->request->getPost('settings');

		if (sproutForms()->settings->saveSettings($settings))
		{
			craft()->userSession->setNotice(Craft::t('Settings saved.'));

			$this->redirectToPostedUrl();
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save settings.'));

			// Send the settings back to the template
			craft()->urlManager->setRouteVariables(array(
				'settings' => $settings
			));
		}
	}
}
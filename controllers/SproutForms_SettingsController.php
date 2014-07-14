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
		$settingsModel = new SproutForms_SettingsModel;

		// Create any variables you want available in your template
		// $variables['items'] = craft()->pluginName->getAllItems();
		$settings = craft()->db->createCommand()
			->select('settings')
			->from('plugins')
			->where('class=:class', array(':class'=> 'SproutForms'))
			->queryScalar();

		$settings = JsonHelper::decode($settings);
		$settingsModel->setAttributes($settings);

		$variables['settings'] = $settingsModel;

		// Load a particular template and with all of the variables you've created
		$this->renderTemplate('sproutforms/settings', $variables);

	}

	public function actionSaveSettings()
	{
		$this->requirePostRequest();
		$settings = craft()->request->getPost('settings');

		if (craft()->sproutForms_settings->saveSettings($settings))
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

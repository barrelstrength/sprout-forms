<?php
namespace Craft;

/**
 * Class SproutFormsPlugin
 *
 * @package Craft
 */
class SproutFormsPlugin extends BasePlugin
{
	/**
	 * @return string
	 */
	public function getName()
	{
		$pluginName         = Craft::t('Sprout Forms');
		$pluginNameOverride = $this->getSettings()->pluginNameOverride;

		return ($pluginNameOverride) ? $pluginNameOverride : $pluginName;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return 'Simple, beautiful forms. 100% control.';
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '2.6.0';
	}

	/**
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '2.4.0';
	}

	/**
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Barrel Strength Design';
	}

	/**
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'http://barrelstrengthdesign.com';
	}

	/**
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return 'https://sprout.barrelstrengthdesign.com/docs/forms';
	}

	/**
	 * @return string
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/barrelstrength/craft-sprout-forms/v2/releases.json';
	}

	/**
	 * @return bool
	 */
	public function hasCpSection()
	{
		if (craft()->userSession->checkPermission('manageSproutFormsForms') ||
				craft()->userSession->checkPermission('viewSproutFormsEntries') ||
			  craft()->userSession->checkPermission('editSproutFormsSettings'))
		{
			return true;
		}
	}

	/**
	 * Get Settings URL
	 */
	public function getSettingsUrl()
	{
		return 'sproutforms/settings';
	}

	public function init()
	{
		Craft::import('plugins.sproutforms.contracts.SproutFormsBaseField');

		Craft::import('plugins.sproutforms.integrations.sproutreports.datasources.*');
		Craft::import('plugins.sproutforms.integrations.sproutimport.SproutForms_EntrySproutImportElementImporter');
		Craft::import('plugins.sproutforms.integrations.sproutimport.SproutForms_FormSproutImportElementImporter');

		Craft::import('plugins.sproutforms.integrations.sproutimport.SproutForms_FormsSproutImportFieldImporter');
		Craft::import('plugins.sproutforms.integrations.sproutimport.SproutForms_EntrySproutImportFieldImporter');

		craft()->on('email.onBeforeSendEmail', array(sproutForms(), 'handleOnBeforeSendEmail'));
		craft()->on('email.onSendEmail', array(sproutForms(), 'handleOnSendEmail'));

		if (craft()->request->isCpRequest() && craft()->request->getSegment(1) == 'sproutforms')
		{
			// @todo Craft 3 - update to use info from config.json
			craft()->templates->includeJsResource('sproutforms/js/brand.js');
			craft()->templates->includeJs("
				sproutFormsBrand = new Craft.SproutBrand();
				sproutFormsBrand.displayFooter({
					pluginName: 'Sprout Forms',
					pluginUrl: 'http://sprout.barrelstrengthdesign.com/craft-plugins/forms',
					pluginVersion: '" . $this->getVersion() . "',
					pluginDescription: '" . $this->getDescription() . "',
					developerName: '(Barrel Strength)',
					developerUrl: '" . $this->getDeveloperUrl() . "'
				});
			");
		}
	}

	/**
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'pluginNameOverride'                  => AttributeType::String,
			'templateFolderOverride'              => AttributeType::String,
			'enablePerFormTemplateFolderOverride' => AttributeType::Bool,
			'enablePayloadForwarding'             => AttributeType::Bool,
			'enableSaveData'                      => AttributeType::Bool,
			'enableSaveDataPerFormBasis'          => AttributeType::Bool,
			'saveDataByDefault'                   => AttributeType::Bool,
		);
	}

	/**
	 * @return array
	 */
	public function registerCpRoutes()
	{
		return array(
			'sproutforms/forms/new'                                     => array(
				'action' => 'sproutForms/forms/editFormTemplate'
			),
			'sproutforms/forms/edit/(?P<formId>\d+)'                    => array(
				'action' => 'sproutForms/forms/editFormTemplate'
			),
			'sproutforms/entries/edit/(?P<entryId>\d+)'                 => array(
				'action' => 'sproutForms/entries/editEntryTemplate'
			),
			'sproutforms/settings/(general|advanced)'                   => array(
				'action' => 'sproutForms/settings/settingsIndexTemplate'
			),
			'sproutforms/settings/entrystatuses'                        => array(
				'action' => 'sproutForms/entryStatuses/index'
			),
			'sproutforms/settings/entrystatuses/new'                    => array(
				'action' => 'sproutForms/entryStatuses/edit'
			),
			'sproutforms/settings/entrystatuses/(?P<entryStatusId>\d+)' => array(
				'action' => 'sproutForms/entryStatuses/edit'
			),
			'sproutforms/forms/(?P<groupId>\d+)'                        =>
				'sproutforms/forms',
		);
	}

	/**
	 * @return array
	 */
	public function registerUserPermissions()
	{
		return array(
			'manageSproutFormsForms' => array(
				'label' => Craft::t('Manage Forms')
			),
			'viewSproutFormsEntries' => array(
				'label'  => Craft::t('View Form Entries'),
				'nested' => array(
					'editSproutFormsEntries' => array(
						'label' => Craft::t('Edit Form Entries')
					)
				)
			),
			'editSproutFormsSettings' => array(
				'label' => Craft::t('Edit Settings')
			)
		);
	}

	/**
	 * Event registrar
	 *
	 * @param string   $event
	 * @param \Closure $callback
	 *
	 * @deprecate Deprecated for version 0.9.0 in favour of defineSproutEmailEvents()
	 */
	public function sproutformsAddEventListener($event, \Closure $callback)
	{
		switch ($event)
		{
			case 'saveEntry':
			{
				// only event supported at this time
				craft()->on('sproutForms.saveEntry', $callback);
				break;
			}
		}
	}

	/**
	 * @return array
	 */
	public function defineSproutEmailEvents()
	{
		$sproutEmail = craft()->plugins->getPlugin('sproutEmail');

		if ($sproutEmail && version_compare($sproutEmail->getVersion(), '0.9.2', '>='))
		{
			require_once dirname(__FILE__) . '/integrations/sproutemail/SproutForms_SaveEntryEvent.php';

			return array(new SproutForms_SaveEntryEvent());
		}

		sproutForms()->log('Sprout Email 0.9.2+ is required for Dynamic Events integration.');
	}

	/**
	 * @return array
	 */
	public function registerSproutReportsDataSources()
	{
		return array(
			new SproutFormsEntriesDataSource()
		);
	}

	/**
	 * Register Sprout Import importers classes for the Sprout Import plugin integration
	 *
	 * @return array
	 */
	public function registerSproutImportImporters()
	{
		return array(
			// Element Importers
			new SproutForms_EntrySproutImportElementImporter(),
			new SproutForms_FormSproutImportElementImporter(),

			// Field Importers
			new SproutForms_FormsSproutImportFieldImporter(),
			new SproutForms_EntrySproutImportFieldImporter()
		);
	}

	/**
	 * Redirects to examples after installation
	 *
	 * @return void
	 */
	public function onAfterInstall()
	{
		sproutForms()->entries->installDefaultEntryStatuses();
		sproutForms()->forms->installDefaultSettings();
		craft()->request->redirect(UrlHelper::getCpUrl() . '/sproutforms/settings/examples');
	}

	/**
	 * @throws \Exception
	 */
	public function onBeforeUninstall()
	{
		$forms = sproutForms()->forms->getAllForms();

		foreach ($forms as $form)
		{
			sproutForms()->forms->deleteForm($form);
		}
	}

	public function registerSproutFormsFields()
	{
		$basePath = craft()->path->getPluginsPath() . 'sproutforms/integrations/sproutforms/fields/';
		require_once $basePath . 'SproutFormsNumberField.php';
		require_once $basePath . 'SproutFormsPlainTextField.php';
		require_once $basePath . 'SproutFormsCheckboxesField.php';
		require_once $basePath . 'SproutFormsDropdownField.php';
		require_once $basePath . 'SproutFormsMultiSelectField.php';
		require_once $basePath . 'SproutFormsRadioButtonsField.php';
		require_once $basePath . 'SproutFormsAssetsField.php';
		require_once $basePath . 'SproutFormsEntriesField.php';
		require_once $basePath . 'SproutFormsCategoriesField.php';
		require_once $basePath . 'SproutFormsTagsField.php';

		return array(
			new SproutFormsNumberField(),
			new SproutFormsPlainTextField(),
			new SproutFormsCheckboxesField(),
			new SproutFormsDropdownField(),
			new SproutFormsMultiSelectField(),
			new SproutFormsRadioButtonsField(),
			new SproutFormsAssetsField(),
			new SproutFormsEntriesField(),
			new SproutFormsCategoriesField(),
			new SproutFormsTagsField(),
		);
	}
}

/**
 * @return SproutFormsService
 */
function sproutForms()
{
	return Craft::app()->getComponent('sproutForms');
}

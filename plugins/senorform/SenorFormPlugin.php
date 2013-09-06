<?php

namespace Craft;

require_once( dirname(__FILE__) . "/helpers/common.php" );

class SenorFormPlugin extends BasePlugin
{
		function getName()
		{
				// @TODO - make this into a function in a helper library
				// we will use it in several addons.

				// The plugin name
				$pluginName = Craft::t('Señor Form');

				// The plugin name override
				// $plugin = craft()->plugins->getPlugin('colonelcategory');
				$plugin = craft()->db->createCommand()
														 ->select('settings')
														 ->from('plugins')
														 ->where('class=:class', array(':class'=> 'SenorForm'))
														 ->queryScalar();

				$plugin = json_decode( $plugin, true );
				$pluginNameOverride = $plugin['pluginNameOverride'];

				return ($pluginNameOverride) ? $pluginNameOverride : $pluginName;
		}

		function getVersion()
		{
				return '1.0';
		}

		function getDeveloper()
		{
				return 'Barrel Strength Design';
		}

		function getDeveloperUrl()
		{
				return 'http://barrelstrengthdesign.com';
		}

		public function hasCpSection()
		{
				return true;
		}


		protected function defineSettings()
		{
				return array(
						'pluginNameOverride'      => AttributeType::String,
				);
		}

		public function getSettingsHtml()
		{
				return craft()->templates->render('senorform/settings/settings', array(
						'settings' => $this->getSettings()
				));
		}


		/**
		 * Register control panel routes
		 */
		public function registerCpRoutes()
		{
				return array(
						'senorform\/forms\/new' =>
						'senorform/forms/_edit',

						'senorform\/forms\/edit\/(?P<formId>\d+)' =>
						'senorform/forms/_edit',

						'senorform\/fields\/new' =>
						'senorform\/fields\/_edit',

						'senorform\/fields\/edit\/(?P<fieldId>\d+)' =>
						'senorform\/fields\/_edit',

					'senorform\/entries\/view\/(?P<entryId>\d+)' =>
					'senorform/entries/_view'
				);
		}

		/**
		 * Register permissions
		 */
		public function registerUserPermissions()
		{
				return array(
						'createForms' => array(
									'label' => Craft::t('Create Forms')
						),
						'viewEntries' => array(
									'label' => Craft::t('View Entries')
						),
						'editSettings' => array(
									'label' => Craft::t('Edit Settings')
						)
				);
		}

		public function masterBlasterTrigger()
		{
			$info = array(
				'hooks' => array(
					1 => array(
						'name' => 'On Form Submit',
						'instructions' => 'Trigger is run every time a form is submitted. Data object shared includes all submitted form data and email object',
						'hook' => 'senorFormAfterSaveAction'
					)
				)
			);
			return $info;
		}

		/**
		 * Setup a Custom Field Group for the fields
		 * that will be associated with Señor Form
		 *
		 * @todo Is this the best way to do this?  What if somebody changes the
		 * field group name or it gets deleted and recreated with a different ID?
		 * Can we just manage this in our own table?  Essentially, the FORMS are
		 * the GROUPS.
		 *
		 */
		public function onAfterInstall()
		{
				// $fieldGroup = array(
				//     'name' => 'Señor Form'
				// );

				// craft()->db->createCommand()
				//            ->insert('fieldgroups', $fieldGroup);
		}



		public function dropTables()
		{
		$contentRecord = new SenorForm_ContentRecord();
		$contentRecord->dropTable();

		$fieldRecord = new SenorForm_FieldRecord();
		$fieldRecord->dropTable();

		$formRecord = new SenorForm_FormRecord();
		$formRecord->dropTable();
		}
}

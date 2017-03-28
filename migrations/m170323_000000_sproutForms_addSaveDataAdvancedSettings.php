<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170323_000000_sproutForms_addSaveDataAdvancedSettings extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'plugins';

		if (craft()->db->tableExists($tableName))
		{

			$pluginSettings = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->where('class =:class', array(':class' => 'SproutForms'))
				->queryRow();

			$settings = isset($pluginSettings['settings']) ? json_decode($pluginSettings['settings'], true) : array();

			$settings['enableSaveData'] = "1";
			$settings['enableSaveDataPerFormBasis'] = "0";
			$settings['saveDataByDefault'] = "1";

			$settings = JsonHelper::encode($settings);

			craft()->db->createCommand()->update('plugins',
				array('settings' => $settings),
				array('class' => 'SproutForms')
			);

			SproutFormsPlugin::log("Added new options: enableSaveData, saveDataByDefault and enableSaveDataPerFormBasis to Advance settings", LogLevel::Info, true);
		}

		$formTable = 'sproutforms_forms';

		$forms = craft()->db->createCommand()
				->select('*')
				->from($formTable)
				->queryAll();

		// by default Sprout Forms save all the submitted data
		foreach ($forms as $form)
		{
			// check if there is any payload to ignore
			if (!$form['submitAction'])
			{
				craft()->db->createCommand()->update($formTable,array('savePayload'=>1), 'id = :id', array(':id' => $form['id']));
			}
		}

		// rename savePayload column
		$this->renameColumn($formTable, 'savePayload', 'saveData');

		return true;
	}
}
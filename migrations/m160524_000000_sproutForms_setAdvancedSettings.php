<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160524_000000_sproutForms_setAdvancedSettings extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutforms_forms';

		if (craft()->db->tableExists($tableName))
		{
			$payloadForward = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->where("submitAction <> ''")
				->queryRow();

			$templateOverride = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->where('enableTemplateOverrides = 1')
				->queryRow();

			$settings = craft()->plugins->getPlugin('sproutforms')->getSettings();
			$settings['enablePerFormTemplateFolderOverride'] = "";
			$settings['enablePayloadForwarding'] = "";

			if ($payloadForward)
			{
				$settings['enablePayloadForwarding'] = "1";
			}

			if ($templateOverride)
			{
				$settings['enablePerFormTemplateFolderOverride'] = "1";
			}

			$settings = JsonHelper::encode($settings);

			craft()->db->createCommand()->update('plugins',
				array('settings' => $settings),
				array('class' => 'SproutForms')
			);

			Craft::log('Set advanced settings after update status id to entries', LogLevel::Info, true);
		}

		return true;
	}
}
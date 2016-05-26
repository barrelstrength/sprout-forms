<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160511_000000_sproutForms_setDefaultColorToEntries extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutforms_entries';

		if (craft()->db->tableExists($tableName))
		{
			$entries = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->queryAll();

			$status = craft()->db->createCommand()
				->select('*')
				->from('sproutforms_entrystatuses')
				->queryRow();

			if ($status)
			{
				foreach ($entries as $entry)
				{
					craft()->db->createCommand()->update($tableName,
							array('statusId'=>$status['id']), 'id = :id', array(':id' => $entry['id']));
				}

				Craft::log('Added default status id for Form Entries.', LogLevel::Info, true);
			}
			else
			{
				Craft::log("Can't set default statusId because sproutforms_entrystatuses table is empty.", LogLevel::Info, true);
			}
		}

		return true;
	}
}
<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141026_000000_sproutForms_addFormGroupsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// ADD A TABLE TO THE DATABASE

		// The Table you wish to add. 'craft_' prefix will be added automatically.
		$tableName = 'sproutforms_formgroups';

		if (!craft()->db->tableExists($tableName))
		{
			Craft::log('Creating the `$tableName` table.', LogLevel::Info, true);

			// Create the craft_sproutforms_formgroups table
			craft()->db->createCommand()->createTable($tableName, array(
				'name' => array(
					'maxLength' => 255,
					'column'    => 'varchar',
					'required'  => true
				),
			), null, true);

			// Add indexes to craft_sproutforms_formgroups
			craft()->db->createCommand()->createIndex($tableName, 'name', true);
		}

		return true;
	}
}
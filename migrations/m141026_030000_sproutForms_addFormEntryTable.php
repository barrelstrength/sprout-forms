<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141026_030000_sproutForms_addFormEntryTable extends BaseMigration
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
		$tableName = 'sproutforms_entries';

		if (!craft()->db->tableExists($tableName))
		{
			Craft::log('Creating the `$tableName` table.', LogLevel::Info, true);

			// Create the craft_sproutforms_entries table
			craft()->db->createCommand()->createTable($tableName, array(
				'id'        => array('column' => 'integer', 'required' => true, 'primaryKey' => true),
				'formId'    => array('column' => 'integer', 'required' => true),
				'ipAddress' => array(),
				'userAgent' => array('column' => 'text'),
			), null, false);

			// Add foreign keys to craft_sproutforms_entries
			craft()->db->createCommand()->addForeignKey($tableName, 'id', 'elements', 'id', 'CASCADE', null);
			craft()->db->createCommand()->addForeignKey($tableName, 'formId', 'sproutforms_forms', 'id', 'CASCADE', null);
		}

		return true;
	}
}
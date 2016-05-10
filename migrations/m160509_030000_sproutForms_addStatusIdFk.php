<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160509_030000_sproutForms_addStatusIdFk extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// specify the table name here
		$tableName  = 'sproutforms_entries';
		$columnName = 'statusId';

		if (craft()->db->columnExists($tableName, $columnName))
		{
			$this->addForeignKey($tableName, 'statusId', 'sproutforms_entrystatuses', 'id', 'CASCADE');
		}
		else
		{
			// tell craft that we couldn't add the fk
			SproutFormsPlugin::log("Column `$columnName` does not exists in the `$tableName` table.", LogLevel::Info, true);
		}

		// return true and let craft know its done
		return true;
	}
}
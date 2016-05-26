<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160509_010000_sproutForms_addStatusIdColumn extends BaseMigration
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

		if (!craft()->db->columnExists($tableName, $columnName))
		{
			$this->addColumnAfter($tableName, $columnName,
				array(
					'column'   => ColumnType::Int
				),
				'formId'
			);
			// log that we created the new column
			SproutFormsPlugin::log("Created the `$columnName` in the `$tableName` table.", LogLevel::Info, true);
		}
		// if the column already exists in the table
		else
		{
			// tell craft that we couldn't create the column as it alredy exists.
			SproutFormsPlugin::log("Column `$columnName` already exists in the `$tableName` table.", LogLevel::Info, true);
		}

		// return true and let craft know its done
		return true;
	}
}

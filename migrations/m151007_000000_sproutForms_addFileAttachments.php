<?php
namespace Craft;

class m151007_000000_sproutForms_addFileAttachments extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// specify the table name here
		$tableName  = 'sproutforms_forms';
		$columnName = 'enableFileAttachments';

		if (!craft()->db->columnExists($tableName, $columnName))
		{
			$this->addColumn($tableName, $columnName,
				array(
					'column'   => ColumnType::TinyInt,
					'length'   => 1,
					'null'     => false,
					'default'  => 0,
					'unsigned' => true
				)
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

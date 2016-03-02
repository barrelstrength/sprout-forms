<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m151006_000000_sproutForms_addTemplateOverrides extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// specify columns and AttributeType
		$columns = array(
			'enableTemplateOverrides' => ColumnType::TinyInt,
			'templateOverridesFolder' => ColumnType::Varchar,
		);

		$this->_addColumns($columns);

		// return true and let craft know its done
		return true;
	}

	private function _addColumns($newColumns)
	{
		// specify the table name here
		$tableName = 'sproutforms_forms';

		foreach ($newColumns as $columnName => $columnType)
		{
			// check if the column does NOT exist
			if (!craft()->db->columnExists($tableName, $columnName))
			{
				if ($columnName == "enableTemplateOverrides")
				{
					$this->addColumn($tableName, $columnName, array(
							'column'   => $columnType,
							'length'   => 1,
							'null'     => false,
							'default'  => 0,
							'unsigned' => true
						)
					);
				}
				else
				{
					$this->addColumn($tableName, $columnName, array(
							'column'  => $columnType,
							'null'    => true,
							'default' => null,
						)
					);
				}
				// log that we created the new column
				SproutFormsPlugin::log("Created the `$columnName` in the `$tableName` table.", LogLevel::Info, true);
			}

			// if the column already exists in the table
			else
			{
				// tell craft that we couldn't create the column as it alredy exists.
				SproutFormsPlugin::log("Column `$columnName` already exists in the `$tableName` table.", LogLevel::Info, true);
			}
		}
	}
}

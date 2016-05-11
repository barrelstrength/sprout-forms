<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160509_000000_sproutForms_addEntryStatusTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutforms_entrystatuses';

		if (!craft()->db->tableExists($tableName))
		{
			Craft::log('Creating the `$tableName` table.', LogLevel::Info, true);

			// Create the craft_sproutforms_entries table
			$this->createTable($tableName, array(
				'id' => array(
					'column'     => ColumnType::PK,
					'required'   => true
				),
				'name' => array(
					'column'   => ColumnType::Varchar,
					'required' => true
				),
				'handle' => array(
					'column'   => ColumnType::Varchar,
					'required' => true
				),
				'color' => array(
					'column'   => ColumnType::Enum,
					'values'   => array('green', 'orange', 'red', 'blue',
					'yellow', 'pink', 'purple', 'turquoise',
					'light', 'grey', 'black'
					),
					'required' => true,
					'default'  => 'blue'
				),
				'sortOrder' => array(
					'column' => ColumnType::SmallInt
				),
				'isDefault' => array(
					'column'  => ColumnType::TinyInt,
					'default' => 0
				),
			));
		}

		return true;
	}
}
<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m131228_045200_sproutforms_AddFieldSortOrder extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{

		$fieldsTable = $this->dbConnection->schema->getTable('{{sproutforms_fields}}');

		if ($fieldsTable)
		{
			if (($sortOrderColumn = $fieldsTable->getColumn('sortOrder')) == null)
			{
				Craft::log('Adding `sortOrder` column to the `sproutforms_fields` table.', LogLevel::Info, true);

				$this->addColumnAfter('sproutforms_fields', 'sortOrder', array('column' => ColumnType::TinyInt), 'validation');

				Craft::log('Added `sortOrder` column to the `sproutforms_fields` table.', LogLevel::Info, true);
			}
			else
			{
				Craft::log('Tried to add a `sortOrder` column to the `sproutforms_fields` table, but it already exists.', LogLevel::Warning);
			}
		}
		else
		{
			Craft::log('Could not find `sproutforms_fields` table.', LogLevel::Error);
		}

		return true;
	}
}

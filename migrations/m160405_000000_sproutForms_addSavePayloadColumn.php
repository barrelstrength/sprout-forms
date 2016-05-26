<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160405_000000_sproutForms_addSavePayloadColumn extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutforms_forms';
		$columnName   = 'savePayload';

		if (!craft()->db->columnExists($tableName, $columnName))
		{

			$this->addColumnAfter($tableName, $columnName,
				array(
					'column'   => ColumnType::TinyInt,
					'required' => false,
					'default'  => 0,
					'length'   => 1,
					'null'     => false,
					'unsigned' => true
				),
				'submitButtonText'
			);

			SproutFormsPlugin::log("Created the column `$columnName` in `$tableName` .", LogLevel::Info, true);
		}
		else
		{
			SproutFormsPlugin::log("Column `$columnName` already existed in `$tableName`.", LogLevel::Info, true);
		}

		return true;
	}
}

<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150418_000000_sproutForms_addNotificationEnabledSetting extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		if (($table = $this->dbConnection->schema->getTable('{{sproutforms_forms}}')))
		{
			if (($column = $table->getColumn('notificationEnabled')) == null)
			{
				$definition = array(
					AttributeType::Bool,
					'column'   => ColumnType::TinyInt,
					'unsigned' => true,
					'null'     => false,
					'length'   => 1,
					'default'  => 0
				);

				$this->addColumnAfter('sproutforms_forms', 'notificationEnabled', $definition, 'submitButtonText');
			}
			else
			{
				Craft::log('Tried to add a `notificationEnabled` column to the `sproutforms_forms` table, but there is already one there.', LogLevel::Warning);
			}
		}
		else
		{
			Craft::log('Could not find the `sproutforms_forms` table.', LogLevel::Error);
		}

		return true;
	}
}
<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140201_120000_sproutForms_addFormButtonSettings extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		
				// ADD A COLUMN TO A TABLE IN THE DATABASE
		
				$sproutFormsTable = $this->dbConnection->schema->getTable('{{sproutforms_forms}}');
		
				if ($sproutFormsTable)
				{
					if (($submitButtonType = $sproutFormsTable->getColumn('submitButtonType')) == null)
					{
						Craft::log('Adding `submitButtonType` column to the `sproutforms_forms` table.', LogLevel::Info, true);
		
						$this->addColumnAfter('sproutforms_forms', 'submitButtonType', array(AttributeType::String, 'required' => false), 'handle');
		
						Craft::log('Added `submitButtonType` column to the `sproutforms_forms` table.', LogLevel::Info, true);
					}
					else
					{
						Craft::log('Tried to add a `submitButtonType` column to the `sproutforms_forms` table, but there is already one there.', LogLevel::Warning);
					}

					if (($submitButtonText = $sproutFormsTable->getColumn('submitButtonText')) == null)
					{
						Craft::log('Adding `submitButtonText` column to the `sproutforms_forms` table.', LogLevel::Info, true);
		
						$this->addColumnAfter('sproutforms_forms', 'submitButtonText', array(AttributeType::String, 'required' => false), 'submitButtonType');
		
						Craft::log('Added `submitButtonText` column to the `sproutforms_forms` table.', LogLevel::Info, true);
					}
					else
					{
						Craft::log('Tried to add a `submitButtonText` column to the `sproutforms_forms` table, but there is already one there.', LogLevel::Warning);
					}
				}
				else
				{
					Craft::log('Could not find an `sproutforms_forms` table. Wut?', LogLevel::Error);
				}
		
		return true;
	}
}
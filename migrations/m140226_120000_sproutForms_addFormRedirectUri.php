<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140226_120000_sproutForms_addFormRedirectUri extends BaseMigration
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
					if (($redirectUriColumn = $sproutFormsTable->getColumn('redirectUri')) == null)
					{
						Craft::log('Adding `redirectUri` column to the `sproutforms_forms` table.', LogLevel::Info, true);
		
						$this->addColumnAfter('sproutforms_forms', 'redirectUri', array(AttributeType::String, 'required' => false), 'handle');
		
						Craft::log('Added `redirectUri` column to the `sproutforms_forms` table.', LogLevel::Info, true);
					}
					else
					{
						Craft::log('Tried to add a `redirectUri` column to the `sproutforms_forms` table, but there is already one there.', LogLevel::Warning);
					}
				}
				else
				{
					Craft::log('Could not find an `sproutforms_forms` table. Wut?', LogLevel::Error);
				}
		
		return true;
	}
}
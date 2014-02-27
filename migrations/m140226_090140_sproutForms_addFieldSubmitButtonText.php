<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140226_090140_sproutForms_addFieldSubmitButtonText extends BaseMigration
{
    private $field = 'submitButtonText';
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{		
				$sproutFormsTable = $this->dbConnection->schema->getTable('{{sproutforms_forms}}');
		
				if ($sproutFormsTable)
				{
				    // add sproutforms_forms.submitButtonType
					if ($sproutFormsTable->getColumn($this->field) == null)
					{
						Craft::log('Adding `' . $this->field . '` column to the `sproutforms_forms` table.', LogLevel::Info, true);
		
						$this->addColumnAfter('sproutforms_forms', $this->field, array(AttributeType::String, 'required' => false), 'email_distribution_list');
		
						Craft::log('Added `' . $this->field . '` column to the `sproutforms_forms` table.', LogLevel::Info, true);
					}
					else
					{
						Craft::log('Tried to add a `' . $this->field . '` column to the `sproutforms_forms` table, but there is already one there.', LogLevel::Warning);
					}
				}
				else
				{
					Craft::log('Could not find an `sproutforms_forms` table. Wut?', LogLevel::Error);
				}
		
		return true;
	}
}
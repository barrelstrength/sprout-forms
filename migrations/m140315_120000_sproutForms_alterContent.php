<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140315_120000_sproutForms_alterContent extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $tableName = 'sproutforms_content';
        $table     = $this->dbConnection->schema->getTable('{{' . $tableName . '}}');
        
        if ($table) {
            $field = 'serverData';
            
            // add sproutforms_forms.serverData
            if ($table->getColumn($field) == null) {
                Craft::log('Adding `' . $field . '` column to the `' . $tableName . '` table.', LogLevel::Info, true);
                
                $this->addColumnBefore($tableName, $field, 'TEXT', 'dateCreated');
                
                Craft::log('Added `' . $field . '` column to the `' . $tableName . '` table.', LogLevel::Info, true);
            } else {
                Craft::log('Tried to add a `' . $field . '` column to the `' . $tableName . '` table, but there is already one there.', LogLevel::Warning);
            }
        } else {
            Craft::log('Could not find an ' . $tableName . ' table. Wut?', LogLevel::Error);
        }
        
        return true;
    }
}
<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140328_235959_sproutForms_alterContent extends BaseMigration
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
            
            $this->alterColumn($tableName, $field, 'TEXT');

        } else {
            Craft::log('Could not find an ' . $tableName . ' table. Wut?');
        }
        
        return true;
    }
}
<?php
/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\base\FieldInterface;
use craft\db\Migration;
use craft\db\Table;
use Craft;

class m210328_000000_clean_up_default_fields_in_content_table extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $db = Craft::$app->getDb();
        $schema = $db->getSchema();
        $tableName = $schema->getRawTableName(Table::CONTENT);
        $schema->refreshTableSchema($tableName);
        $table = $db->getTableSchema($tableName);

        // Get all content table columns
        $columnNames = $table->getColumnNames();

        $customColumnNames = array_filter($columnNames, function($columnName) {
            return strpos($columnName, Craft::$app->content->fieldColumnPrefix) === 0;
        });

        // Get all global fields
        $allFields = Craft::$app->getFields()->getAllFields();

        $globalFields = array_filter($allFields, function(FieldInterface $field) {
            return strpos($field->context, 'global') === 0;
        });

        // Append fieldColumnPrefix to the field handles and make them
        // the array keys so we can easily check if they exist later
        $globalFieldHandles = array_flip(array_map(function($handle) {
            return Craft::$app->content->fieldColumnPrefix . $handle;
        }, array_column($globalFields, 'handle')));

        $orphanedColumns = [];

        // Check if a custom column has a matching field
        foreach($customColumnNames as $customColumnName) {
            // If a custom field column doesn't have a matching custom field handle
            if (isset($globalFieldHandles[$customColumnName])) {
                continue;
            }

            // the prefix of the fields Sprout Forms created by accident
            $orphanFieldPrefix = Craft::$app->content->fieldColumnPrefix . 'defaultField';

            // If field doesn't start with field_defaultField[XYZ]
            if (!strpos($customColumnName, $orphanFieldPrefix) === 0) {
                continue;
            }

            // If no matching content table column is found
            if (!Craft::$app->getMigrator()->db->columnExists(Table::CONTENT, $customColumnName)) {
                continue;
            };

            // Still here? Drop our errant field and log it
            $this->dropColumn(Table::CONTENT, $customColumnName);

            Craft::info("Dropped column $customColumnName from " . Table::CONTENT);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m210328_000000_clean_up_default_fields_in_content_table cannot be reverted.\n";

        return false;
    }
}

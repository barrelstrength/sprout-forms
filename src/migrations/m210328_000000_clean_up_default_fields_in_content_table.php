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
use craft\helpers\ElementHelper;
use craft\helpers\FieldHelper;

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

        $globalFields = array_filter($allFields, static function(FieldInterface $field) {
            return $field->context === 'global';
        });

        $globalFieldColumnNames = [];

        foreach ($globalFields as $field) {
            if (!$field::hasContentColumn()) {
                continue;
            }

            if (is_array($field->getContentColumnType())) {
                $columnKeys = array_keys($field->getContentColumnType());
                foreach($columnKeys as $columnKey) {
                    $globalFieldColumnNames[] = ElementHelper::fieldColumnFromField($field, $columnKey);
                }

                continue;
            }

            if (is_string($field->getContentColumnType())) {
                $globalFieldColumnNames[] = ElementHelper::fieldColumnFromField($field);
            }
        }

        // the prefix of the fields Sprout Forms created by accident
        $orphanFieldPrefix = Craft::$app->content->fieldColumnPrefix . 'defaultField';

        // Check if a custom column has a matching field
        foreach($customColumnNames as $customColumnName) {
            // If a custom field column is in array of things we don't own, continue
            if (in_array($customColumnName, $globalFieldColumnNames, true)) {
                continue;
            }

            // If field doesn't start with field_defaultField[XYZ]
            if (strpos($customColumnName, $orphanFieldPrefix) !== 0) {
                continue;
            }

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

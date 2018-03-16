<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SingleLine;
use Craft;
use craft\helpers\MigrationHelper;

/**
 * m180314_161540_craft2_to_craft3 migration.
 */
class m180314_161540_craft2_to_craft3 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $newSettings = [
            'placeholder' => '',
            'multiline' => '',
            'initialRows' => '4',
            'charLimit' => '255',
            'columnType' => 'string'
        ];

        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Phone'])
            ->andWhere('context LIKE "%sproutForms:%"')
            ->all();

        foreach ($fields as $field) {
            $settingsAsJson = json_encode($newSettings);
            $this->update('{{%fields}}', ['type' => SingleLine::class, 'settings' => $settingsAsJson], ['id' => $field['id']], [], false);
        }

        $forms = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        foreach ($forms as $form) {
            $table = "{{%sproutformscontent_".$form['handle']."}}";
            // P&T already add a site column
            if ($this->db->columnExists($table, 'locale__siteId')) {
                MigrationHelper::renameColumn($table, 'locale__siteId', 'siteId', $this);
            }else{
                // let's do it manually
                $siteId = Craft::$app->getSites()->getPrimarySite()->id;
                $isNew = false;

                if (!$this->db->columnExists($table, 'siteId')) {

                    $this->addColumn($table, 'siteId', $this->integer()->after('elementId')->notNull());
                    $isNew = true;
                }

                $rows = (new Query())
                ->select(['id'])
                ->from([$table])
                ->all();

                foreach ($rows as $row) {
                    $this->update($table, ['siteId' => $siteId], ['id' => $row['id']], [], false);
                }

                if ($isNew) {
                    $this->createIndex($this->db->getIndexName($table, 'elementId,siteId'), $table, 'elementId,siteId', true);

                    $this->addForeignKey($this->db->getForeignKeyName($table, 'siteId'), $table, 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');
                }

            }

            if ($this->db->columnExists($table, 'locale')) {
                MigrationHelper::dropForeignKeyIfExists($table, ['locale'], $this);
                $this->dropColumn($table, 'locale');
            }

        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_161540_craft2_to_craft3 cannot be reverted.\n";
        return false;
    }
}

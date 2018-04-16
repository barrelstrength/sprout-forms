<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;

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
        $forms = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        foreach ($forms as $form) {
            $table = '{{%sproutformscontent_'.$form['handle'].'}}';
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

            if ($this->db->columnExists($table, 'locale')) {
                MigrationHelper::dropIndexIfExists($table, ['elementId', 'locale'], true, $this);
                MigrationHelper::dropIndexIfExists($table, ['locale'], false, $this);
                MigrationHelper::dropForeignKeyIfExists($table, ['locale'], $this);
                $this->dropColumn($table, 'locale');
            }

            if ($this->db->columnExists($table, 'locale__siteId')) {
                MigrationHelper::dropIndexIfExists($table, ['elementId', 'locale__siteId'], true, $this);
                MigrationHelper::dropIndexIfExists($table, ['locale__siteId'], false, $this);
                MigrationHelper::dropForeignKeyIfExists($table, ['locale__siteId'], $this);
                $this->dropColumn($table, 'locale__siteId');
            }
        }

        if ($this->db->columnExists('{{%sproutforms_forms}}', 'enableTemplateOverrides')) {
            $this->dropColumn('{{%sproutforms_forms}}', 'enableTemplateOverrides');
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
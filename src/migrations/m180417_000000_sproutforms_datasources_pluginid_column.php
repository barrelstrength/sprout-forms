<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * sproutforms_datasources_pluginid_column migration.
 */
class sproutforms_datasources_pluginid_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $table = '{{%sproutreports_datasources}}';

        if (!$this->db->columnExists($table, 'pluginId')) {
            $this->addColumn($table, 'pluginId', $this->string()->after('id'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "sproutforms_datasources_pluginid_column cannot be reverted.\n";
        return false;
    }
}

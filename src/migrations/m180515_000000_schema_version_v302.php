<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbase\app\reports\migrations\m180515_000000_rename_notification_pluginId_column;
use barrelstrength\sproutbase\app\reports\migrations\m180515_000001_rename_datasources_pluginId_column;
use craft\db\Migration;

/**
 * m180515_000000_schema_version_v302 migration.
 */
class m180515_000000_schema_version_v302 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $dataSourcePluginIdMigration = new m180515_000001_rename_datasources_pluginId_column();

        ob_start();
        $dataSourcePluginIdMigration->safeUp();
        ob_end_clean();

        $notificationPluginIdMigration = new m180515_000000_rename_notification_pluginId_column();

        ob_start();
        $notificationPluginIdMigration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180515_000000_schema_version_v302 cannot be reverted.\n";
        return false;
    }
}

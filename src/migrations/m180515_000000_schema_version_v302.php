<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbase\app\email\migrations\m180515_000000_rename_notification_pluginId_column;
use barrelstrength\sproutbase\app\email\migrations\m180515_000001_update_notification_element_types;
use barrelstrength\sproutbase\app\email\migrations\m180515_000002_rename_notification_options_column;
use barrelstrength\sproutbase\app\reports\migrations\m180515_000001_rename_datasources_pluginId_column;
use barrelstrength\sproutbase\app\reports\migrations\m180515_000002_update_report_element_types;
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
        
        $notificationOptionsMigration = new m180515_000002_rename_notification_options_column();

        ob_start();
        $notificationOptionsMigration->safeUp();
        ob_end_clean();

        $notificationElementTypeMigration = new m180515_000001_update_notification_element_types();

        ob_start();
        $notificationElementTypeMigration->safeUp();
        ob_end_clean();

        $reportElementTypeMigration = new m180515_000002_update_report_element_types();

        ob_start();
        $reportElementTypeMigration->safeUp();
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

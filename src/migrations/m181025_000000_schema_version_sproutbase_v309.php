<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\migrations\m180501_000001_update_notification_element_types;
use barrelstrength\sproutbaseemail\migrations\m180501_000002_rename_notification_options_column;
use barrelstrength\sproutbaseemail\migrations\m180501_000003_add_notification_columns;
use barrelstrength\sproutbaseemail\migrations\m180501_000004_update_element_types;
use barrelstrength\sproutbaseemail\migrations\m180501_000005_update_copypaste_type;
use barrelstrength\sproutbaseemail\migrations\m180515_000000_rename_notification_pluginId_column;
use barrelstrength\sproutbaseemail\migrations\m180515_000003_update_notification_eventId_types;
use barrelstrength\sproutbaseemail\migrations\m181026_000000_update_notification_data;
use barrelstrength\sproutbasereports\migrations\m180515_000002_update_report_element_types;
use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m180515_000000_schema_version_v302 migration.
 */
class m181025_000000_schema_version_sproutbase_v309 extends Migration
{
    /**
     * @inheritdoc
     * @throws NotSupportedException
     * @throws NotSupportedException
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $reportElementTypeMigration = new m180515_000002_update_report_element_types();
        ob_start();
        $reportElementTypeMigration->safeUp();
        ob_end_clean();

        $notificationElementTypeMigration = new m180501_000001_update_notification_element_types();
        ob_start();
        $notificationElementTypeMigration->safeUp();
        ob_end_clean();

        $notificationOptionsMigration = new m180501_000002_rename_notification_options_column();
        ob_start();
        $notificationOptionsMigration->safeUp();
        ob_end_clean();

        $notificationAddMigration = new m180501_000003_add_notification_columns();
        ob_start();
        $notificationAddMigration->safeUp();
        ob_end_clean();

        $notificationUpdateMigration = new m180501_000004_update_element_types();
        ob_start();
        $notificationUpdateMigration->safeUp();
        ob_end_clean();

        $notificationUpdateMigration = new m180501_000005_update_copypaste_type();
        ob_start();
        $notificationUpdateMigration->safeUp();
        ob_end_clean();

        $notificationPluginIdMigration = new m180515_000000_rename_notification_pluginId_column();
        ob_start();
        $notificationPluginIdMigration->safeUp();
        ob_end_clean();

        $notificationEmailTemplateIdMigration = new m180515_000003_update_notification_eventId_types();
        ob_start();
        $notificationEmailTemplateIdMigration->safeUp();
        ob_end_clean();

        $notificationEmailDataMigration = new m181026_000000_update_notification_data();
        ob_start();
        $notificationEmailDataMigration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180515_000000_schema_version_v302 cannot be reverted.\n";

        return false;
    }
}

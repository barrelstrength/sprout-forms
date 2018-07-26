<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbase\app\email\migrations\m180725_080639_add_notification_columns;
use craft\db\Migration;

/**
 * m180725_000000_schema_version_v305 migration.
 */
class m180725_000000_schema_version_v305 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $addNotificationColumnsMigration = new m180725_080639_add_notification_columns();

        ob_start();
        $addNotificationColumnsMigration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180725_000000_schema_version_v305 cannot be reverted.\n";
        return false;
    }
}

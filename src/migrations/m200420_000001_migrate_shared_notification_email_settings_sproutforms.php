<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\migrations\m200420_000001_migrate_shared_notification_email_settings;
use craft\db\Migration;

class m200420_000001_migrate_shared_notification_email_settings_sproutforms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m200420_000001_migrate_shared_notification_email_settings();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200420_000001_migrate_shared_notification_email_settings_sproutforms cannot be reverted.\n";

        return false;
    }
}

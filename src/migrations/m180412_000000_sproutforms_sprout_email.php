<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\migrations\Install as SproutBaseNotificationInstall;
use craft\db\Migration;

/**
 * m180412_000000_sproutforms_sprout_email migration.
 */
class m180412_000000_sproutforms_sprout_email extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Install Sprout Notifications Table
        $this->installSproutEmail();

        return true;
    }

    public function installSproutEmail()
    {
        $migration = new SproutBaseNotificationInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180412_000000_sproutforms_sprout_email cannot be reverted.\n";

        return false;
    }
}

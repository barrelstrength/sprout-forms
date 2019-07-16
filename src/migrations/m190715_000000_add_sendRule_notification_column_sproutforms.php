<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;
use barrelstrength\sproutbaseemail\migrations\m190715_000000_add_sendRule_notification_column;

/**
 * m190715_000000_add_sendRule_notification_column_sproutforms migration.
 */
class m190715_000000_add_sendRule_notification_column_sproutforms extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $migration = new m190715_000000_add_sendRule_notification_column();

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
        echo "m190715_000000_add_sendRule_notification_column_sproutforms cannot be reverted.\n";
        return false;
    }
}

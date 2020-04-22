<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\migrations\m200420_000000_migrate_shared_sent_email_settings;
use craft\db\Migration;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class m200420_000000_migrate_shared_sent_email_settings_sproutforms extends Migration
{
    /**
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        $migration = new m200420_000000_migrate_shared_sent_email_settings();

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
        echo "m200420_000000_migrate_shared_sent_email_settings_sproutforms cannot be reverted.\n";

        return false;
    }
}

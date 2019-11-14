<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\db\Migration;
use yii\base\Exception;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m191008_000000_add_spam_status extends Migration
{
    /**
     * @return bool
     * @throws Exception
     */
    public function safeUp(): bool
    {
        $entryStatus = SproutForms::$app->entries->getEntryStatusByHandle('spam');

        if ($entryStatus->id) {
            Craft::info('Spam status already exists');
            return true;
        }

        $entryStatus->name = 'Spam';
        $entryStatus->handle = 'spam';
        $entryStatus->color = 'black';
        $entryStatus->isDefault = false;
        SproutForms::$app->entries->saveEntryStatus($entryStatus);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191008_000000_add_spam_status cannot be reverted.\n";

        return false;
    }
}

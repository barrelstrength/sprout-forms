<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m191008_000000_add_spam_status extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $entryStatus = SproutForms::$app->entries->getEntryStatusByHandle(EntryStatus::SPAM_STATUS_HANDLE);

        if ($entryStatus->id) {
            Craft::info("Spam status already exists");
            return true;
        }

        $entryStatus->name = "Spam";
        $entryStatus->handle = EntryStatus::SPAM_STATUS_HANDLE;
        $entryStatus->color = "black";
        $entryStatus->isDefault = false;
        SproutForms::$app->entries->saveEntryStatus($entryStatus);
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

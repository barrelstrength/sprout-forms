<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\Entries;
use craft\fields\Entries as CraftEntries;
use craft\helpers\Json;

/**
 * m180314_161528_sproutforms_entries_fields migration.
 */
class m180314_161528_sproutforms_entries_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $entriesFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftEntries::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($entriesFields as $entryField) {
            $settings = Json::decode($entryField['settings']);
            $settings['source'] = $settings['source'] ?? null;
            $settings['targetSiteId'] = null;
            $settings['localizeRelations'] = false;
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', ['type' => Entries::class, 'settings' => $settingsAsJson], ['id' => $entryField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161528_sproutforms_entries_fields cannot be reverted.\n";
        return false;
    }
}

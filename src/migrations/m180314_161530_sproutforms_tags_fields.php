<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\Tags;
use craft\fields\Tags as CraftTags;
use craft\helpers\Json;

/**
 * m180314_161530_sproutforms_tags_fields migration.
 */
class m180314_161530_sproutforms_tags_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tagFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftTags::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($tagFields as $tagField) {
            $settings = Json::decode($tagField['settings']);
            $settings['sources'] = '*';
            $settings['targetSiteId'] = null;
            $settings['viewMode'] = 'large';
            $settings['limit'] = null;
            $settings['localizeRelations'] = false;
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', ['type' => Tags::class, 'settings' => $settingsAsJson], ['id' => $tagField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161530_sproutforms_tags_fields cannot be reverted.\n";
        return false;
    }
}

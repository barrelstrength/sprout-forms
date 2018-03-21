<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Tags;
use craft\fields\Tags as CraftTags;

/**
 * m180314_161530_sproutforms_tags_fields migration.
 */
class m180314_161530_sproutforms_tags_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tagFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftTags::class])
            ->andWhere('context LIKE "%sproutForms:%"')
            ->all();

        foreach ($tagFields as $tagField) {
            $settings = json_decode($tagField['settings'], true);
            $settings['sources'] = '*';
            $settings['targetSiteId'] = null;
            $settings['viewMode'] = 'large';
            $settings['limit'] = null;
            $settings['localizeRelations'] = false;
            $settingsAsJson = json_encode($settings);

            $this->update('{{%fields}}', ['type' => Tags::class, 'settings' => $settingsAsJson], ['id' => $tagField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_161530_sproutforms_tags_fields cannot be reverted.\n";
        return false;
    }
}

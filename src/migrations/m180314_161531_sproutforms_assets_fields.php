<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\FileUpload;
use craft\fields\Assets as CraftAssets;

/**
 * m180314_161531_sproutforms_assets_fields migration.
 */
class m180314_161531_sproutforms_assets_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftAssets::class])
            ->andWhere('context LIKE "%sproutForms:%"')
            ->all();

        foreach ($fields as $field) {
            $settings = json_decode($field['settings'], true);
            $settings['sources'] = '*';
            $settings['sources'] = null;
            $settings['useSingleFolder'] = 1;
            $settings['defaultUploadLocationSource'] = $settings['defaultUploadLocationSource'] ? 'folder:'.$settings['defaultUploadLocationSource'] : '';
            $settings['singleUploadLocationSource'] = $settings['singleUploadLocationSource'] ? 'folder:'.$settings['singleUploadLocationSource'] : '';
            $settings['viewMode'] = 'large';
            $settings['localizeRelations'] = false;
            $settingsAsJson = json_encode($settings);

            $this->update('{{%fields}}', ['type' => FileUpload::class, 'settings' => $settingsAsJson], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_161531_sproutforms_assets_fields cannot be reverted.\n";
        return false;
    }
}

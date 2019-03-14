<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\FileUpload;
use craft\fields\Assets as CraftAssets;
use craft\helpers\Json;

/**
 * m180314_161531_sproutforms_assets_fields migration.
 */
class m180314_161531_sproutforms_assets_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftAssets::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $settings = Json::decode($field['settings']);
            $settings['sources'] = '*';
            $settings['useSingleFolder'] = 1;
            $settings['defaultUploadLocationSource'] = $settings['defaultUploadLocationSource'] ? 'folder:'.$settings['defaultUploadLocationSource'] : '';
            $settings['singleUploadLocationSource'] = $settings['singleUploadLocationSource'] ? 'folder:'.$settings['singleUploadLocationSource'] : '';
            $settings['viewMode'] = 'large';
            $settings['localizeRelations'] = false;
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', ['type' => FileUpload::class, 'settings' => $settingsAsJson], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161531_sproutforms_assets_fields cannot be reverted.\n";
        return false;
    }
}

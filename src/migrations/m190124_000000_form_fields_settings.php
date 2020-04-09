<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;

class m190124_000000_form_fields_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        $folderIds = [];
        $sectionIds = [];
        $tagGroupIds = [];
        $categoryGroupIds = [];

        foreach ($fields as $field) {
            if ($field['settings']) {
                $settings = Json::decodeIfJson($field['settings']) ?: [];
            } else {
                $settings = [];
            }

            switch ($field['type']) {
                case 'barrelstrength\sproutforms\fields\formfields\FileUpload':
                    if (!empty($settings['defaultUploadLocationSource']) && strpos($settings['defaultUploadLocationSource'], ':') !== false) {
                        list(, $folderIds[]) = explode(':', $settings['defaultUploadLocationSource']);
                    }
                    if (!empty($settings['singleUploadLocationSource']) && strpos($settings['singleUploadLocationSource'], ':') !== false) {
                        list(, $folderIds[]) = explode(':', $settings['singleUploadLocationSource']);
                    }

                    break;
                case 'barrelstrength\sproutforms\fields\formfields\Entries':
                    if (!empty($settings['sources']) && is_array($settings['sources'])) {
                        foreach ($settings['sources'] as $source) {
                            if (strpos($source, ':') !== false) {
                                list(, $sectionIds[]) = explode(':', $source);
                            }
                        }
                    }

                    break;
                case 'barrelstrength\sproutforms\fields\formfields\Categories':
                    if (!empty($settings['source']) && strpos($settings['source'], ':') !== false) {
                        list(, $categoryGroupIds[]) = explode(':', $settings['source']);
                    }

                    break;
                case 'barrelstrength\sproutforms\fields\formfields\Tags':
                    if (!empty($settings['source']) && strpos($settings['source'], ':') !== false) {
                        list(, $tagGroupIds[]) = explode(':', $settings['source']);
                    }

                    break;
            }
        }

        $folders = (new Query())
            ->select(['id', 'uid'])
            ->from([Table::VOLUMEFOLDERS])
            ->where(['id' => $folderIds])
            ->pairs();

        $sections = (new Query())
            ->select(['id', 'uid'])
            ->from([Table::SECTIONS])
            ->where(['id' => $sectionIds])
            ->pairs();

        $tagGroups = (new Query())
            ->select(['id', 'uid'])
            ->from([Table::TAGGROUPS])
            ->where(['id' => $tagGroupIds])
            ->pairs();

        $categoryGroups = (new Query())
            ->select(['id', 'uid'])
            ->from([Table::CATEGORYGROUPS])
            ->where(['id' => $categoryGroupIds])
            ->pairs();

        foreach ($fields as $field) {
            if ($field['settings']) {
                $settings = Json::decodeIfJson($field['settings']) ?: [];
            } else {
                $settings = [];
            }

            switch ($field['type']) {
                case 'barrelstrength\sproutforms\fields\formfields\FileUpload':
                    if (!empty($settings['defaultUploadLocationSource']) && strpos($settings['defaultUploadLocationSource'], ':') !== false) {
                        $default = explode(':', $settings['defaultUploadLocationSource']);
                        $settings['defaultUploadLocationSource'] = isset($folders[$default[1]]) ? $default[0].':'.$folders[$default[1]] : null;
                    }

                    if (!empty($settings['singleUploadLocationSource']) && strpos($settings['singleUploadLocationSource'], ':') !== false) {
                        $single = explode(':', $settings['singleUploadLocationSource']);
                        $settings['singleUploadLocationSource'] = isset($folders[$single[1]]) ? $single[0].':'.$folders[$single[1]] : null;
                    }

                    break;
                case 'barrelstrength\sproutforms\fields\formfields\Entries':
                    if (!empty($settings['sources']) && is_array($settings['sources'])) {
                        $newSources = [];

                        foreach ($settings['sources'] as $source) {
                            $source = explode(':', $source);
                            if (count($source) > 1) {
                                $newSources[] = $source[0].':'.($sections[$source[1]] ?? $source[1]);
                            } else {
                                $newSources[] = $source[0];
                            }
                        }

                        $settings['sources'] = $newSources;
                    }

                    break;
                case 'barrelstrength\sproutforms\fields\formfields\Categories':
                    if (!empty($settings['source']) && strpos($settings['source'], ':') !== false) {
                        $source = explode(':', $settings['source']);
                        $settings['source'] = $source[0].':'.($categoryGroups[$source[1]] ?? $source[1]);
                    }

                    break;
                case 'barrelstrength\sproutforms\fields\formfields\Tags':
                    if (!empty($settings['source']) && strpos($settings['source'], ':') !== false) {
                        $source = explode(':', $settings['source']);
                        $settings['source'] = $source[0].':'.($tagGroups[$source[1]] ?? $source[1]);
                    }

                    break;
            }

            $settings = Json::encode($settings);

            $this->update(Table::FIELDS, ['settings' => $settings], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190124_000000_form_fields_settings cannot be reverted.\n";

        return false;
    }
}

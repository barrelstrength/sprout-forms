<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\SectionHeading;
use craft\helpers\Json;

/**
 * m180314_161538_sproutforms_notes_fields migration.
 */
class m180314_161538_sproutforms_notes_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Notes'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $settings = Json::decode($field['settings']);
            $settings['notes'] = $settings['instructions'] ?? '';
            unset($settings['instructions'], $settings['style']);
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', ['type' => SectionHeading::class, 'settings' => $settingsAsJson], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161538_sproutforms_notes_fields cannot be reverted.\n";
        return false;
    }
}

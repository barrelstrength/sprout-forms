<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SectionHeading;

/**
 * m180314_161538_sproutforms_notes_fields migration.
 */
class m180314_161538_sproutforms_notes_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Notes'])
            ->andWhere('context LIKE "%sproutForms:%"')
            ->all();

        foreach ($fields as $field) {
            $settings = json_decode($field['settings'], true);
            $settings['notes'] = $settings['instructions'] ?? '';
            unset($settings['instructions']);
            $settingsAsJson = json_encode($settings);

            $this->update('{{%fields}}', ['type' => SectionHeading::class, 'settings' => $settingsAsJson], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_161538_sproutforms_notes_fields cannot be reverted.\n";
        return false;
    }
}

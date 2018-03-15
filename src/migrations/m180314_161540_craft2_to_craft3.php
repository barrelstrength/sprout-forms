<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SingleLine;

/**
 * m180314_161540_craft2_to_craft3 migration.
 */
class m180314_161540_craft2_to_craft3 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $newSettings = [
            'placeholder' => '',
            'multiline' => '',
            'initialRows' => '4',
            'charLimit' => '255',
            'columnType' => 'string'
        ];

        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Phone'])
            ->andWhere('context LIKE "%sproutForms:%"')
            ->all();

        foreach ($fields as $field) {
            $settingsAsJson = json_encode($newSettings);
            $this->update('{{%fields}}', ['type' => SingleLine::class, 'settings' => $settingsAsJson], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_161540_craft2_to_craft3 cannot be reverted.\n";
        return false;
    }
}

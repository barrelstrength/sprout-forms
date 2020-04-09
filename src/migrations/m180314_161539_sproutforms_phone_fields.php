<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class m180314_161539_sproutforms_phone_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $newSettings = [
            'placeholder' => '',
            'charLimit' => '255',
            'columnType' => 'string'
        ];

        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Phone'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $settingsAsJson = Json::encode($newSettings);
            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\SingleLine',
                'settings' => $settingsAsJson
            ], [
                'id' => $field['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161539_sproutforms_phone_fields cannot be reverted.\n";

        return false;
    }
}

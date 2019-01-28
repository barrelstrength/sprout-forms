<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\Number;
use craft\fields\Number as CraftNumber;
use craft\helpers\Json;

/**
 * m180314_161527_sproutforms_number_fields migration.
 */
class m180314_161527_sproutforms_number_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $numberFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftNumber::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($numberFields as $numberField) {
            $settings = Json::decode($numberField['settings'], true);
            $settings['size'] = '';
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', ['type' => Number::class, 'settings' => $settingsAsJson], ['id' => $numberField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_161527_sproutforms_number_fields cannot be reverted.\n";
        return false;
    }
}

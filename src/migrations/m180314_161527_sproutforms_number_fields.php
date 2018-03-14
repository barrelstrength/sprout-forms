<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Number;
use craft\fields\Number as CraftNumber;

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
            ->andWhere('context LIKE "%sproutForms:%"')
            ->all();

        foreach ($numberFields as $numberField) {
        		$settings = json_decode($numberField['settings'], true);
        		$settings['size'] = '';
        		$settingsAsJson = json_encode($settings);

            $this->update('{{%fields}}', ['type' => Number::class, 'settings' => $settingsAsJson]], ['id' => $numberField['id']], [], false);
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

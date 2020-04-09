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

class m180314_161527_sproutforms_number_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $numberFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'craft\fields\Number'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($numberFields as $numberField) {
            $settings = Json::decode($numberField['settings']);
            $settings['size'] = '';
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\Number',
                'settings' => $settingsAsJson
            ], [
                'id' => $numberField['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161527_sproutforms_number_fields cannot be reverted.\n";

        return false;
    }
}

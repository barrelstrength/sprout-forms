<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;

class m180314_161534_sproutforms_link_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Link'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\Url'
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
        echo "m180314_161534_sproutforms_link_fields cannot be reverted.\n";

        return false;
    }
}

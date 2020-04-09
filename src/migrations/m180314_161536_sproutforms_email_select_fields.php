<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;

class m180314_161536_sproutforms_email_select_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_EmailSelect'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\EmailDropdown'
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
        echo "m180314_161536_sproutforms_email_select_fields cannot be reverted.\n";

        return false;
    }
}

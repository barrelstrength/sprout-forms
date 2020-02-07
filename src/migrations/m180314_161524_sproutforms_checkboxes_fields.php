<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\fields\formfields\Checkboxes;
use craft\db\Migration;
use craft\db\Query;
use craft\fields\Checkboxes as CraftCheckboxes;

/**
 * m180314_161524_sproutforms_checkboxes_fields migration.
 */
class m180314_161524_sproutforms_checkboxes_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $checkboxesFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftCheckboxes::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($checkboxesFields as $checkboxesField) {
            $this->update('{{%fields}}', ['type' => Checkboxes::class], ['id' => $checkboxesField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161524_sproutforms_checkboxes_fields cannot be reverted.\n";

        return false;
    }
}

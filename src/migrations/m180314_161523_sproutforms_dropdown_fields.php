<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\Dropdown;
use craft\fields\Dropdown as CraftDropdown;

/**
 * m180314_161523_sproutforms_dropdown_fields migration.
 */
class m180314_161523_sproutforms_dropdown_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $dropdownFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftDropdown::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($dropdownFields as $dropdownField) {
            $this->update('{{%fields}}', ['type' => Dropdown::class], ['id' => $dropdownField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161523_sproutforms_dropdown_fields cannot be reverted.\n";
        return false;
    }
}

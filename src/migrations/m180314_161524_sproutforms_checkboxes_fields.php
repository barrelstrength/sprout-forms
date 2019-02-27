<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\Checkboxes;
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

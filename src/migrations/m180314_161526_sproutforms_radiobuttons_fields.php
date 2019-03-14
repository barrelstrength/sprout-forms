<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\MultipleChoice;
use craft\fields\RadioButtons as CraftRadioButtons;

/**
 * m180314_161526_sproutforms_radiobuttons_fields migration.
 */
class m180314_161526_sproutforms_radiobuttons_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $radioButtonsFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => CraftRadioButtons::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($radioButtonsFields as $radioButtonsField) {
            $this->update('{{%fields}}', ['type' => MultipleChoice::class], ['id' => $radioButtonsField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161526_sproutforms_radiobuttons_fields cannot be reverted.\n";
        return false;
    }
}

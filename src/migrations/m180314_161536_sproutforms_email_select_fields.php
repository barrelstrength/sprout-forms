<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\EmailDropdown;

/**
 * m180314_161536_sproutforms_email_select_fields migration.
 */
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
            $this->update('{{%fields}}', ['type' => EmailDropdown::class], ['id' => $field['id']], [], false);
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

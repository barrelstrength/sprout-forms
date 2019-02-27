<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\Email;

/**
 * m180314_161535_sproutforms_email_fields migration.
 */
class m180314_161535_sproutforms_email_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Email'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $this->update('{{%fields}}', ['type' => Email::class], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161535_sproutforms_email_fields cannot be reverted.\n";
        return false;
    }
}

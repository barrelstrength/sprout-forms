<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\Hidden;

/**
 * m180314_161532_sproutforms_hidden_fields migration.
 */
class m180314_161532_sproutforms_hidden_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Hidden'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $this->update('{{%fields}}', ['type' => Hidden::class], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161532_sproutforms_hidden_fields cannot be reverted.\n";
        return false;
    }
}

<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\RegularExpression;

/**
 * m180314_161537_sproutforms_regular_expressions_fields migration.
 */
class m180314_161537_sproutforms_regular_expressions_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_RegularExpression'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $this->update('{{%fields}}', ['type' => RegularExpression::class], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161537_sproutforms_regular_expressions_fields cannot be reverted.\n";
        return false;
    }
}

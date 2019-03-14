<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\fields\formfields\Paragraph;
use barrelstrength\sproutforms\fields\formfields\SingleLine;
use craft\db\Migration;
use craft\db\Query;

/**
 * m180611_000000_paragraph_column_type migration.
 */
class m180611_000000_paragraph_column_type extends Migration
{
    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $forms = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        foreach ($forms as $form) {
            $handle = $form['handle'];
            $table = '{{%sproutformscontent_'.$handle.'}}';

            $context = 'sproutForms:'.$form['id'];
            $fields = (new Query())
                ->select(['id', 'handle', 'settings', 'type'])
                ->from(['{{%fields}}'])
                ->where(['context' => $context])
                ->all();

            foreach ($fields as $field) {
                if ($field['type'] == SingleLine::class || $field['type'] == Paragraph::class) {
                    $column = 'field_'.$field['handle'];
                    if ($this->db->columnExists($table, $column)) {
                        $this->alterColumn($table, $column, $this->text());
                    }
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180611_000000_paragraph_column_type cannot be reverted.\n";
        return false;
    }
}

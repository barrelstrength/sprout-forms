<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

class m180611_000000_paragraph_column_type extends Migration
{
    /**
     * @inheritdoc
     * @throws NotSupportedException
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
                if ($field['type'] == 'barrelstrength\sproutforms\fields\formfields\SingleLine' || $field['type'] == 'barrelstrength\sproutforms\fields\formfields\Paragraph') {
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

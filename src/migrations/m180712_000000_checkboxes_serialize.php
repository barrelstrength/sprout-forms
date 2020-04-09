<?php /** @noinspection ClassConstantCanBeUsedInspection */
/** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use yii\base\NotSupportedException;

class m180712_000000_checkboxes_serialize extends Migration
{
    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'barrelstrength\sproutforms\fields\formfields\Checkboxes'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        $forms = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        foreach ($forms as $form) {
            $contentTable = '{{%sproutformscontent_'.$form['handle'].'}}';

            foreach ($fields as $field) {
                $column = 'field_'.$field['handle'];

                if ($this->db->columnExists($contentTable, $column)) {

                    $entries = (new Query())
                        ->select(['id', $column])
                        ->from([$contentTable])
                        ->all();

                    foreach ($entries as $entry) {
                        $newValue = [];
                        $value = $entry[$column];
                        $values = Json::decode($value);

                        if ($values) {
                            foreach ($values as $value) {
                                if (isset($value['value'])) {
                                    $newValue[] = $value['value'];
                                }
                            }
                        }

                        if ($newValue) {
                            $newValueAsJson = Json::encode($newValue);
                            $this->update($contentTable, [$column => $newValueAsJson], ['id' => $entry['id']], [], false);
                        }
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
        echo "m180712_000000_checkboxes_serialize cannot be reverted.\n";

        return false;
    }
}

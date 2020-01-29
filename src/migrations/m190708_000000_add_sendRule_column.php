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

/**
 * m190708_000000_add_sendRule_column migration.
 */
class m190708_000000_add_sendRule_column extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_integrations}}';

        if (!$this->db->columnExists($table, 'sendRule')) {
            $this->addColumn($table, 'sendRule', $this->text()->after('type'));
        }

        $integrations = (new Query())
            ->select(['id'])
            ->from(['{{%sproutforms_integrations}}'])
            ->all();

        foreach ($integrations as $integration) {
            $this->update('{{%sproutforms_integrations}}', ['sendRule' => '*'], ['id' => $integration['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190708_000000_add_sendRule_column cannot be reverted.\n";

        return false;
    }
}

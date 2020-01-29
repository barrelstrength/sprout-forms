<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m191116_000002_add_referrer_column migration.
 */
class m191116_000002_add_referrer_column extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_entries}}';

        if (!$this->db->columnExists($table, 'referrer')) {
            $this->addColumn($table, 'referrer', $this->string()->after('ipAddress'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000002_add_referrer_column cannot be reverted.\n";

        return false;
    }
}

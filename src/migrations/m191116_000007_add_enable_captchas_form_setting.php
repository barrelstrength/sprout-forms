<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m191116_000007_add_enable_captchas_form_setting migration.
 */
class m191116_000007_add_enable_captchas_form_setting extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_forms}}';

        if (!$this->db->columnExists($table, 'enableCaptchas')) {
            $this->addColumn($table, 'enableCaptchas', $this->boolean()->after('formTemplate')->defaultValue(true)->notNull());
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000007_add_enable_captchas_form_setting cannot be reverted.\n";

        return false;
    }
}

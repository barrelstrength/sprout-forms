<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

class m200322_000000_add_form_method_and_message_columns extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutforms_forms}}';

        if (!$this->db->columnExists($table, 'submissionMethod')) {
            $this->addColumn($table, 'submissionMethod', $this->string()->defaultValue('sync')->notNull()->after('redirectUri'));
            $this->addColumn($table, 'errorDisplayMethod', $this->string()->defaultValue('inline')->notNull()->after('submissionMethod'));
            $this->addColumn($table, 'successMessage', $this->text()->after('errorDisplayMethod'));
            $this->addColumn($table, 'errorMessage', $this->text()->after('successMessage'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200322_000000_add_form_method_and_message_columns cannot be reverted.\n";

        return false;
    }
}

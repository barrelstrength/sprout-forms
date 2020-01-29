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
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m191005_000003_remove_enableFileAttachments_column extends Migration
{
    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%sproutforms_forms}}', 'enableFileAttachments')) {
            // Migration has already run
            return;
        }

        $this->dropColumn('{{%sproutforms_forms}}', 'enableFileAttachments');
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191005_000003_remove_enableFileAttachments_column cannot be reverted.\n";

        return false;
    }
}

<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\records\Form;
use craft\db\Migration;
use yii\base\NotSupportedException;

class m200418_000001_rename_formTemplateId_column extends Migration
{
    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function safeUp()
    {
        if (!$this->db->columnExists(Form::tableName(), 'formTemplateId')) {
            // Migration has already run
            $this->renameColumn(Form::tableName(), 'formTemplate', 'formTemplateId');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200418_000001_rename_formTemplateId_column cannot be reverted.\n";

        return false;
    }
}

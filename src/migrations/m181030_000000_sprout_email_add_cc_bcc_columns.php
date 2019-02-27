<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\migrations\m180927_080639_add_cc_bcc_columns as CcBccMigration;
use craft\db\Migration;

/**
 * m181030_000000_sprout_email_add_cc_bcc_columns migration.
 */
class m181030_000000_sprout_email_add_cc_bcc_columns extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp(): bool
    {
        $ccBccMigration = new CcBccMigration();

        ob_start();
        $ccBccMigration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m181030_000000_sprout_email_add_cc_bcc_columns cannot be reverted.\n";
        return false;
    }
}

<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use barrelstrength\sproutbaseemail\migrations\m191202_000004_add_sent_emails_elements;

class m191202_000000_add_sent_emails_elements_sproutforms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m191202_000004_add_sent_emails_elements();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191202_000000_add_sent_emails_elements_sproutforms cannot be reverted.\n";
        return false;
    }
}

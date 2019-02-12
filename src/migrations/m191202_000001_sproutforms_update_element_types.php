<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\migrations\m191202_000004_update_element_types;
use craft\db\Migration;

class m191202_000001_sproutforms_update_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new m191202_000004_update_element_types();

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
        echo "m191202_000001_sproutforms_update_element_types cannot be reverted.\n";
        return false;
    }
}

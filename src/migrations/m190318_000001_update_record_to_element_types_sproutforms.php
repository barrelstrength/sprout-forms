<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use barrelstrength\sproutbasereports\migrations\m190305_000002_update_record_to_element_types as BaseUpdateElements;

/**
 * m190318_000001_update_record_to_element_types_sproutforms migration.
 */
class m190318_000001_update_record_to_element_types_sproutforms extends Migration
{
    /**
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function safeUp(): bool
    {
        $migration = new BaseUpdateElements();

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
        echo "m190318_000001_update_record_to_element_types_sproutforms cannot be reverted.\n";
        return false;
    }
}

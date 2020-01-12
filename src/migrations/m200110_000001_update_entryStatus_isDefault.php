<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutseo\elements\Redirect;
use craft\db\Migration;

class m200110_000001_update_entryStatus_isDefault extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->update('{{%sproutforms_entrystatuses}}', [
            'isDefault' => 0
        ], [
            'isDefault' => null
        ], [], false);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200110_000001_update_entryStatus_isDefault cannot be reverted.\n";
        return false;
    }
}

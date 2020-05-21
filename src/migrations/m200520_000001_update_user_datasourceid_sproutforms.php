<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\m200520_000001_update_user_datasourceid;
use craft\db\Migration;

class m200520_000001_update_user_datasourceid_sproutforms extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $migration = new m200520_000001_update_user_datasourceid();

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
        echo "m200520_000001_update_user_datasourceid_sproutforms cannot be reverted.\n";

        return false;
    }
}

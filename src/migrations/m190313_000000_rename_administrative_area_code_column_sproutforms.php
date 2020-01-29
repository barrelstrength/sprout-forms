<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasefields\migrations\m190313_000000_add_administrativeareacode_column;
use craft\db\Migration;
use yii\base\NotSupportedException as NotSupportedExceptionAlias;

/**
 * m190313_000000_rename_administrative_area_code_column_sproutforms migration.
 */
class m190313_000000_rename_administrative_area_code_column_sproutforms extends Migration
{
    /**
     * @inheritDoc
     *
     * @throws NotSupportedExceptionAlias
     */
    public function safeUp(): bool
    {
        $migration = new m190313_000000_add_administrativeareacode_column();

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
        echo "m190313_000000_rename_administrative_area_code_column_sproutforms cannot be reverted.\n";

        return false;
    }
}

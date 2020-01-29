<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m191116_000001_rename_integration_log_datasource migration.
 */
class m191116_000001_rename_integration_log_datasource extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $oldDataSourceType = 'barrelstrength\sproutforms\integrations\sproutreports\datasources\SubmissionLogDataSource';
        $newDataSourceType = 'barrelstrength\sproutforms\integrations\sproutreports\datasources\IntegrationLogDataSource';

        // Update our existing or new Data Source
        $this->update('{{%sproutreports_datasources}}', [
            'type' => $newDataSourceType
        ], [
            'type' => $oldDataSourceType
        ], [], false);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000001_rename_integration_log_datasource cannot be reverted.\n";

        return false;
    }
}

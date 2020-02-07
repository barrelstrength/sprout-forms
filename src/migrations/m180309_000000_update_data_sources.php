<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbasereports\migrations\Install as SproutBaseReportsInstall;
use barrelstrength\sproutbasereports\migrations\m180307_042132_craft3_schema_changes as SproutReportsCraft2toCraft3Migration;
use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m180309_000000_update_data_sources migration.
 */
class m180309_000000_update_data_sources extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $this->installSproutReports();

        // Run our Sprout Reports migration in Sprout Base
        $migration = new SproutReportsCraft2toCraft3Migration();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $oldDataSourceId = 'sproutforms.entries';
        $dataSourceClass = 'barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource';

        $query = new Query();

        // See if our old data source exists
        $dataSource = $query->select('*')
            ->from(['{{%sproutreports_datasources}}'])
            ->where(['type' => $oldDataSourceId])
            ->one();

        if ($dataSource === null) {
            // If not, see if our new Data Source exists
            $dataSource = $query->select('*')
                ->from(['{{%sproutreports_datasources}}'])
                ->where(['type' => $dataSourceClass])
                ->one();
        }

        // If we don't have a Data Source record, no need to do anything
        if ($dataSource === null) {
            $this->insert('{{%sproutreports_datasources}}', [
                'type' => $dataSourceClass,
                'allowNew' => 1
            ]);
            $dataSource['id'] = $this->db->getLastInsertID('{{%sproutreports_datasources}}');
            $dataSource['allowNew'] = 1;
        }

        // Update our existing or new Data Source
        $this->update('{{%sproutreports_datasources}}', [
            'type' => $dataSourceClass,
            'allowNew' => $dataSource['allowNew'] ?? 1
        ], [
            'id' => $dataSource['id']
        ], [], false);

        // Update any related dataSourceIds in our Reports table
        $this->update('{{%sproutreports_reports}}', [
            'dataSourceId' => $dataSource['id']
        ], [
            'dataSourceId' => $oldDataSourceId
        ], [], false);

        return true;
    }

    public function installSproutReports()
    {
        $migration = new SproutBaseReportsInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180309_000000_update_data_sources cannot be reverted.\n";

        return false;
    }
}

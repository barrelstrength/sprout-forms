<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;

/**
 * m191116_000003_add_default_entry_status_datasource_settings migration.
 */
class m191116_000003_add_default_entry_status_datasource_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $dataSourceType = 'barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource';

        $entryStatusIds = (new Query())
            ->select(['id'])
            ->from(['{{%sproutforms_entrystatuses}}'])
            ->where(['not in', 'handle', ['spam']])
            ->column();

        $formEntriesReports = (new Query())
            ->select([
                'reports.id',
                'reports.dataSourceId',
                'reports.settings'
            ])
            ->from(['{{%sproutreports_reports}} reports'])
            ->leftJoin(['{{%sproutreports_datasources}} datasources'], '[[datasources.id]] = [[reports.dataSourceId]]')
            ->where(['type' => $dataSourceType])
            ->all();

        foreach ($formEntriesReports as $formEntriesReport) {
            $settings = json_decode($formEntriesReport['settings'], false);

            if (!isset($settings->entryStatusIds)) {
                $settings->entryStatusIds = $entryStatusIds;
            }

            $this->update('{{%sproutreports_reports}}', ['settings' => json_encode($settings)], ['id' => $formEntriesReport['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000003_add_default_entry_status_datasource_settings cannot be reverted.\n";

        return false;
    }
}

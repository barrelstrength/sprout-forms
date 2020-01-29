<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;

/**
 * m191116_000006_add_integration_and_spam_datasources migration.
 */
class m191116_000006_add_integration_and_spam_datasources extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $dataSourceClasses = [
            'barrelstrength\sproutforms\integrations\sproutreports\datasources\IntegrationLogDataSource',
            'barrelstrength\sproutforms\integrations\sproutreports\datasources\SpamLogDataSource'
        ];

        foreach ($dataSourceClasses as $dataSourceClass) {
            $dataSourceExists = (new Query())
                ->select('id')
                ->from(['{{%sproutreports_datasources}}'])
                ->where(['type' => $dataSourceClass])
                ->exists();

            if (!$dataSourceExists) {
                $this->insert('{{%sproutreports_datasources}}', [
                    'type' => $dataSourceClass,
                    'viewContext' => 'sprout-forms',
                    'allowNew' => 1
                ]);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191116_000006_add_integration_and_spam_datasources cannot be reverted.\n";

        return false;
    }
}

<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class m180314_161528_sproutforms_entries_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $entriesFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'craft\fields\Entries'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($entriesFields as $entryField) {
            $settings = Json::decode($entryField['settings']);
            $settings['source'] = $settings['source'] ?? null;
            $settings['targetSiteId'] = null;
            $settings['localizeRelations'] = false;
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\Entries',
                'settings' => $settingsAsJson
            ], [
                'id' => $entryField['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161528_sproutforms_entries_fields cannot be reverted.\n";

        return false;
    }
}

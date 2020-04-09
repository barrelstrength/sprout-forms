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

class m200212_000000_sproutforms_users_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $usersFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'craft\fields\Users'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($usersFields as $usersField) {
            $settings = Json::decode($usersField['settings']);
            $settings['cssClasses'] = '';
            $settings['usernameFormat'] = 'fullName';
            $settings['sources'] = $settings['sources'] ?? null;
            $settings['source'] = null;
            $settings['targetSiteId'] = null;
            $settings['viewMode'] = 'large';
            $settings['limit'] = $settings['limit'] ?? null;
            $settings['selectionLabel'] = $settings['selectionLabel'] ?? null;
            $settings['localizeRelations'] = false;
            $settings['validateRelatedElements'] = false;
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\Users',
                'settings' => $settingsAsJson
            ], [
                'id' => $usersField['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200212_000000_sproutforms_users_fields cannot be reverted.\n";

        return false;
    }
}

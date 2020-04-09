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

/**
 * m180314_161529_sproutforms_categories_fields migration.
 */
class m180314_161529_sproutforms_categories_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $categoriesFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'craft\fields\Categories'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($categoriesFields as $categoryField) {
            $settings = Json::decode($categoryField['settings']);
            $settings['branchLimit'] = $settings['limit'] ?? null;
            $settings['targetSiteId'] = null;
            $settings['localizeRelations'] = false;
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\Categories',
                'settings' => $settingsAsJson
            ], [
                'id' => $categoryField['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161529_sproutforms_categories_fields cannot be reverted.\n";

        return false;
    }
}

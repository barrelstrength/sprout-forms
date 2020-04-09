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

class m180314_161538_sproutforms_notes_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'SproutFields_Notes'])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($fields as $field) {
            $settings = Json::decode($field['settings']);
            $settings['notes'] = $settings['instructions'] ?? '';
            unset($settings['instructions'], $settings['style']);
            $settingsAsJson = Json::encode($settings);

            $this->update('{{%fields}}', [
                'type' => 'barrelstrength\sproutforms\fields\formfields\SectionHeading',
                'settings' => $settingsAsJson
            ], [
                'id' => $field['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161538_sproutforms_notes_fields cannot be reverted.\n";

        return false;
    }
}

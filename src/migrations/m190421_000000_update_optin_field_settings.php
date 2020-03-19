<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use Throwable;

class m190421_000000_update_optin_field_settings extends Migration
{
    /**
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $optinFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'barrelstrength\sproutforms\fields\formfields\OptIn'])
            ->all();

        foreach ($optinFields as $optinField) {
            $settings = json_decode($optinField['settings'], false);

            if (!isset($settings->optInValueWhenTrue)) {
                $settings->optInValueWhenTrue = 'Yes';
            }

            if (!isset($settings->optInValueWhenFalse)) {
                $settings->optInValueWhenFalse = 'No';
            }

            $this->update('{{%fields}}', [
                'settings' => json_encode($settings)
            ], [
                'id' => $optinField['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190421_000000_update_optin_field_settings cannot be reverted.\n";

        return false;
    }
}

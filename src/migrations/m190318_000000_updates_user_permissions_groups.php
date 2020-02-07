<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m190318_000000_updates_user_permissions_groups migration.
 */
class m190318_000000_updates_user_permissions_groups extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $permissions = [
            0 => [
                'oldType' => 'manageSproutFormsForms',
                'newType' => 'sproutForms-editForms'
            ],
            1 => [
                'oldType' => 'viewSproutFormsEntries',
                'newType' => 'sproutForms-viewFormEntries'
            ],
            2 => [
                'oldType' => 'editSproutFormsEntries',
                'newType' => 'sproutForms-editFormEntries'
            ],
            4 => [
                'oldType' => 'editSproutReports',
                'newType' => 'sproutForms-editReports'
            ]
        ];

        foreach ($permissions as $permission) {
            $this->update('{{%userpermissions}}', [
                'name' => strtolower($permission['newType'])
            ], ['name' => strtolower($permission['oldType'])], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190318_000000_updates_user_permissions_groups cannot be reverted.\n";

        return false;
    }
}

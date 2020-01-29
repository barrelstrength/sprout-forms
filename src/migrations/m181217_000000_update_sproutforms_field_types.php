<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m181217_000000_update_sproutforms_field_types migration.
 */
class m181217_000000_update_sproutforms_field_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $fieldClasses = [
            0 => [
                'oldType' => 'SproutForms_Forms',
                'newType' => 'barrelstrength\sproutforms\fields\Forms'
            ],
            1 => [
                'oldType' => 'SproutForms_Entry',
                'newType' => 'barrelstrength\sproutforms\fields\Entries'
            ]
        ];

        foreach ($fieldClasses as $fieldClass) {
            $this->update('{{%fields}}', [
                'type' => $fieldClass['newType']
            ], ['type' => $fieldClass['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m181217_000000_update_sproutforms_field_types cannot be reverted.\n";

        return false;
    }
}

<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

class m200418_000002_update_formTemplateId_values extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $settings = [
            0 => [
                'oldId' => 'sproutforms-accessibletemplates',
                'newId' => 'barrelstrength\sproutforms\formtemplates\AccessibleTemplates'
            ],
            1 => [
                'oldId' => 'sproutforms-basictemplates',
                'newId' => 'barrelstrength\sproutforms\formtemplates\BasicTemplates'
            ]
        ];

        foreach ($settings as $setting) {
            $this->update('{{%sproutforms_forms}}', [
                'formTemplateId' => $setting['newId']
            ], ['formTemplateId' => $setting['oldId']], [], false);
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

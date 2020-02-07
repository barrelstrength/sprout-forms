<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;

/**
 * m180515_000001_update_field_types migration.
 */
class m180515_000001_update_field_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $seedClasses = [
            0 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Categories',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Categories'
            ],
            1 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Checkboxes',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Checkboxes'
            ],
            2 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\CustomHtml',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\CustomHtml'
            ],
            3 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Dropdown',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Dropdown'
            ],
            4 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Email',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Email'
            ],
            5 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\EmailDropdown',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\EmailDropdown'
            ],
            6 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Entries',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Entries'
            ],
            7 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\FileUpload',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\FileUpload'
            ],
            8 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Hidden',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Hidden'
            ],
            9 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Invisible',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Invisible'
            ],
            10 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\MultipleChoice',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\MultipleChoice'
            ],
            11 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\MultiSelect',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\MultiSelect'
            ],
            12 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Name',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Name'
            ],
            13 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Number',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Number'
            ],
            14 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Paragraph',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Paragraph'
            ],
            15 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Phone',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Phone'
            ],
            16 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\PrivateNotes',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\PrivateNotes'
            ],
            17 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\RegularExpression',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\RegularExpression'
            ],
            18 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\SectionHeading',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\SectionHeading'
            ],
            19 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\SingleLine',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\SingleLine'
            ],
            20 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Tags',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Tags'
            ],
            21 => [
                'oldType' => 'barrelstrength\sproutforms\integrations\sproutforms\fields\Url',
                'newType' => 'barrelstrength\sproutforms\fields\formfields\Url'
            ]
        ];

        foreach ($seedClasses as $seedClass) {
            $this->update('{{%fields}}', [
                'type' => $seedClass['newType']
            ], ['type' => $seedClass['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180515_000001_update_field_types cannot be reverted.\n";

        return false;
    }
}

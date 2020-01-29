<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m191008_000000_add_spam_status extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $spamStatusExists = (new Query())
            ->select(['id'])
            ->from(['{{%sproutforms_entrystatuses}}'])
            ->where(['handle' => 'spam'])
            ->exists();

        if ($spamStatusExists) {
            Craft::info('Spam status already exists');

            return true;
        }

        $this->insert('{{%sproutforms_entrystatuses}}', [
            'name' => 'Spam',
            'handle' => 'spam',
            'color' => 'black',
            'isDefault' => false
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191008_000000_add_spam_status cannot be reverted.\n";

        return false;
    }
}

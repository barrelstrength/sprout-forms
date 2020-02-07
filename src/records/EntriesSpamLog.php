<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;

/**
 * Class EntriesSpamLog record.
 *
 * @property $id
 * @property $entryId
 * @property $type
 * @property $errors
 */
class EntriesSpamLog extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_entries_spam_log}}';
    }
}
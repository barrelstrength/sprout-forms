<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;

/**
 * Class EntryStatus record
 *
 * @property int    $id     ID
 * @property string $cpEditUrl
 * @property string $name   Name
 * @property string $handle Handle
 * @property string $color
 * @property int    $sortOrder
 * @property bool   $isDefault
 */
class EntryStatus extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_entrystatuses}}';
    }
}
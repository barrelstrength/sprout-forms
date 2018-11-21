<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;

/**
 * Class Integration record.
 *
 * @property $id
 * @property $formId
 * @property $type
 * @property $settings
 */
class Integration extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_integrations}}';
    }
}
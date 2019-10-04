<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;

/**
 * Class Rules record.
 *
 * @property $id
 * @property $formId
 * @property $name
 * @property $type
 * @property $rules
 * @property $behaviorAction
 * @property $behaviorTarget
 * @property $settings
 * @property $enabled
 */
class Rules extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_rules}}';
    }
}
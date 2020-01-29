<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

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
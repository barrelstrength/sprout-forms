<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;
use barrelstrength\sproutforms\base\Integration as IntegrationApi;

/**
 * Class ConditionalLogic record.
 *
 * @property                                                   $id
 * @property                                                   $formId
 * @property                                                   $name
 * @property                                                   $type
 * @property                                                   $rules
 * @property                                                   $behaviorAction
 * @property                                                   $behaviorTarget
 * @property                                                   $settings
 * @property                                                   $enabled
 */
class ConditionalLogic extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_conditionals}}';
    }
}
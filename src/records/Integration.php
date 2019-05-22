<?php

namespace barrelstrength\sproutforms\records;

use barrelstrength\sproutforms\SproutForms;
use craft\db\ActiveRecord;
use barrelstrength\sproutforms\base\Integration as IntegrationApi;

/**
 * Class Integration record.
 *
 * @property                                                   $id
 * @property                                                   $formId
 * @property                                                   $name
 * @property                                                   $type
 * @property                                                   $settings
 * @property null|IntegrationApi                               $integrationApi
 * @property                                                   $enabled
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
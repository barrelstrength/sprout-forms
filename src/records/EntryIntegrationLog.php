<?php

namespace barrelstrength\sproutforms\records;

use barrelstrength\sproutforms\SproutForms;
use craft\db\ActiveRecord;
use barrelstrength\sproutforms\base\Integration as IntegrationApi;
use Craft;

/**
 * Class Integration record.
 *
 * @property $id
 * @property $entryId
 * @property $integrationId
 * @property $message
 * @property $isValid
 */
class EntryIntegrationLog extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_integrations_entries}}';
    }
}
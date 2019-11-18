<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;

/**
 * Class Integration record.
 *
 * @property $id
 * @property $entryId
 * @property $integrationId
 * @property $message
 * @property $success
 * @property $status
 */
class IntegrationLog extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_integrations_log}}';
    }
}
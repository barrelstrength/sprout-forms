<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\records;

use barrelstrength\sproutforms\base\Integration as IntegrationApi;
use craft\db\ActiveRecord;

/**
 * Class Integration record.
 *
 * @property                                                   $id
 * @property                                                   $formId
 * @property                                                   $name
 * @property                                                   $type
 * @property                                                   $sendRule
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
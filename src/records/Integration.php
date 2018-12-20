<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;
use barrelstrength\sproutforms\base\Integration as IntegrationApi;

/**
 * Class Integration record.
 *
 * @property $id
 * @property $formId
 * @property $name
 * @property $type
 * @property $settings
 * @property $enabled
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

    /**
     * @return null|IntegrationApi
     */
    public function getIntegrationApi()
    {
        $integrationApi = null;

        if($this->type){
            $integrationApi = new $this->type;
            if ($this->settings){
                $settings = json_decode($this->settings, true);
                $integrationApi->setAttributes($settings, false);
            }
        }

        return $integrationApi;
    }
}
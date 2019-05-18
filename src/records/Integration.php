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

    /**
     * @return null|IntegrationApi
     */
    public function getIntegrationApi()
    {
        /** @var IntegrationApi $integrationApi */
        $integrationApi = null;

        if ($this->type) {
            $integrationApi = new $this->type;
            $form = SproutForms::$app->forms->getFormById($this->formId);
            $integrationApi->form = $form;
            $integrationApi->name = $this->name;
            $integrationApi->integrationId = $this->id;
            $integrationApi->enabled = $this->enabled;

            if ($this->settings) {
                $settings = json_decode($this->settings, true);
                $integrationApi->setAttributes($settings, false);
            }
        }

        return $integrationApi;
    }
}
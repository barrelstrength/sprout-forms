<?php

namespace barrelstrength\sproutforms\models;

use barrelstrength\sproutforms\SproutForms;
use craft\base\Model;
use Craft;

class EntryIntegration extends Model
{
    /**
     * @event OnAfterIntegrationSubmit The event that is triggered when the integration is submitted
     */
    const EVENT_AFTER_INTEGRATION_SUBMIT = 'afterIntegrationSubmit';

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null
     */
    public $entryId;

    /**
     * @var int
     */
    public $integrationId;

    /**
     * @var bool
     */
    public $isValid;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * Use the translated section name as the string representation.
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return Craft::t('sprout-forms', $this->id);
    }

    /**
     * @return \barrelstrength\sproutforms\base\Integration|null
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\base\InvalidConfigException
     */
    public function getIntegration()
    {
        $integration = SproutForms::$app->integrations->getIntegrationById($this->integrationId);

        return $integration;
    }
}
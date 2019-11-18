<?php

namespace barrelstrength\sproutforms\events;

use barrelstrength\sproutforms\models\IntegrationLog;
use yii\base\Event;

/**
 * OnAfterIntegrationSubmit class.
 */
class OnAfterIntegrationSubmit extends Event
{
    /**
     * @var IntegrationLog
     */
    public $integrationLog;
}

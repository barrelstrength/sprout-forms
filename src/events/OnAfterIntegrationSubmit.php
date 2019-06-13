<?php

namespace barrelstrength\sproutforms\events;

use barrelstrength\sproutforms\models\EntryIntegration;
use yii\base\Event;

/**
 * OnAfterIntegrationSubmit class.
 */
class OnAfterIntegrationSubmit extends Event
{
    /**
     * @var EntryIntegration
     */
    public $entryIntegration;
}

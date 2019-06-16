<?php

namespace barrelstrength\sproutforms\events;

use barrelstrength\sproutforms\models\SubmissionLog;
use yii\base\Event;

/**
 * OnAfterIntegrationSubmit class.
 */
class OnAfterIntegrationSubmit extends Event
{
    /**
     * @var SubmissionLog
     */
    public $submissionLog;
}

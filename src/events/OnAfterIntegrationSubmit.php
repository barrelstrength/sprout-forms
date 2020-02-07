<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

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

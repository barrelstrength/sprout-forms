<?php

namespace barrelstrength\sproutforms\events;

use yii\base\Event;

/**
 * OnBeforeSaveEntryEvent class.
 */
class OnBeforeSaveEntryEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var ElementEntry
     */
    public $entry = null;

    public $isValid = true;
    public $fakeIt = false;
}

<?php

namespace barrelstrength\sproutforms\events;

use yii\base\Event;
use barrelstrength\sproutforms\elements\Entry;

/**
 * OnBeforeSaveEntryEvent class.
 */
class OnBeforeSaveEntryEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var Entry
     */
    public $entry = null;

    public $isValid = true;
    public $fakeIt = false;
}

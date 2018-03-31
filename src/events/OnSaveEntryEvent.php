<?php

namespace barrelstrength\sproutforms\events;

use barrelstrength\sproutforms\elements\Entry;
use yii\base\Event;

/**
 * OnSaveEntryEvent class.
 */
class OnSaveEntryEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var Entry
     */
    public $entry;

    /**
     * @var bool
     */
    public $isNewEntry = true;
}

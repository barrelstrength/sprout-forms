<?php

namespace barrelstrength\sproutforms\events;

use yii\base\Event;
use barrelstrength\sproutforms\elements\Entry;

/**
 * OnBeforeSaveEntryEvent class.
 */
class OnBeforeSaveEntryEvent extends Event
{
    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var Entry
     */
    public $entry;

    /**
     * @var bool
     */
    public $isValid = true;

    /**
     * @var bool
     */
    public $fakeIt = false;
}

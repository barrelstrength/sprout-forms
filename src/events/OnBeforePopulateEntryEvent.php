<?php

namespace barrelstrength\sproutforms\events;

use yii\base\Event;

/**
 * OnBeforePopulateEntryEvent class.
 */
class OnBeforePopulateEntryEvent extends Event
{
    // Properties
    // =========================================================================

    public $form = null;

    public $isValid = true;
    public $fakeIt = false;
}

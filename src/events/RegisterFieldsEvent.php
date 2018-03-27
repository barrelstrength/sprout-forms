<?php

namespace barrelstrength\sproutforms\events;

use yii\base\Event;

/**
 * RegisterFieldsEvent class.
 */
class RegisterFieldsEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array The registered Fields.
     */
    public $fields = [];
}

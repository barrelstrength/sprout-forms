<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\events;

use barrelstrength\sproutforms\elements\Entry;
use yii\base\Event;

/**
 * OnBeforeSaveEntryEvent class.
 */
class OnBeforeSaveEntryEvent extends Event
{
    /**
     * The Form Entry being saved
     *
     * @var Entry
     */
    public $entry;

    /**
     * Set isValid to false to stop the Entry from being saved.
     *
     * @var bool
     */
    public $isValid = true;

    /**
     * Any errors defined on the Event will be added to the Form Entry model if isValid is set to false
     *
     * @var array
     */
    public $errors = [];
}

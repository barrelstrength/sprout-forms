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
 * OnSaveEntryEvent class.
 */
class OnSaveEntryEvent extends Event
{
    /**
     * @var Entry
     */
    public $entry;

    /**
     * @var bool
     */
    public $isNewEntry = true;
}

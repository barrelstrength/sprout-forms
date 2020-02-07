<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\events;

use barrelstrength\sproutforms\elements\Form;
use yii\base\Event;

/**
 * OnBeforePopulateEntryEvent class.
 */
class OnBeforePopulateEntryEvent extends Event
{
    /**
     * @var Form
     */
    public $form;
}
